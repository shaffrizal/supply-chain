<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\User;
use App\Models\Watchlist;
use App\Services\RiskScoreService;
use App\Services\SentimentAnalysisService;
use App\Services\CountryNewsRiskService;
use App\Services\TrendDataService;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectReadinessTest extends TestCase
{
    public function test_bootstrap_five_shell_replaces_adminlte_runtime(): void
    {
        $this->actingAs($this->user());
        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('bootstrap@5.3.8', false)
            ->assertSee('bootstrap5-shell.css', false)
            ->assertDontSee('adminlte.min.css', false)
            ->assertDontSee('bootstrap@4', false);
    }

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'api.frankfurter.dev')) {
                return Http::response([
                    ['date' => '2026-07-20', 'base' => 'USD', 'quote' => 'IDR', 'rate' => 16210.5],
                    ['date' => '2026-07-21', 'base' => 'USD', 'quote' => 'IDR', 'rate' => 16225.75],
                ]);
            }
            if (str_contains($request->url(), 'api.open-meteo.com')) {
                $current = [
                    'time' => '2026-07-22T12:00', 'temperature_2m' => 29.5,
                    'precipitation' => 2.5, 'rain' => 2.5, 'weather_code' => 96,
                    'wind_speed_10m' => 48, 'wind_gusts_10m' => 67,
                ];
                $latitude = (string) ($request['latitude'] ?? '');

                return Http::response(str_contains($latitude, ',')
                    ? [['current' => $current], ['current' => $current]]
                    : ['current' => $current]);
            }

            return Http::response([], 200);
        });
    }

    public function test_primary_pages_are_available(): void
    {
        $this->actingAs($this->user());
        foreach (['/', '/countries', '/ports', '/shipping-routes', '/watchlists', '/weather', '/weather-map', '/exchange-rate', '/economy', '/news', '/risk-score', '/map', '/comparison'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_port_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->admin());
        $payload = [
            'port_code' => 'IDTST', 'port_name' => 'Test International Port',
            'country_code' => 'ID', 'country' => 'Indonesia', 'city' => 'Jakarta',
            'latitude' => -6.1, 'longitude' => 106.8, 'port_type' => 'Seaport',
            'annual_capacity' => 100000, 'status' => 'Active', 'risk_index' => 35,
        ];

        $response = $this->post(route('admin.ports.store'), $payload);
        $port = Port::where('port_code', 'IDTST')->firstOrFail();
        $response->assertRedirect(route('admin.ports.index'));
        $this->assertSame('Low', $port->risk_level);

        $this->put(route('admin.ports.update', $port), [...$payload, 'risk_index' => 75])
            ->assertRedirect(route('admin.ports.index'));
        $this->assertSame('High', $port->fresh()->risk_level);

        $this->delete(route('admin.ports.destroy', $port))->assertRedirect(route('admin.ports.index'));
        $this->assertDatabaseMissing('ports', ['id' => $port->id]);
    }

    public function test_port_validation_rejects_invalid_coordinates_and_score(): void
    {
        $this->actingAs($this->admin());
        $this->from(route('admin.ports.create'))->post(route('admin.ports.store'), [
            'port_name' => 'Invalid Port', 'country' => 'Indonesia',
            'latitude' => 120, 'longitude' => 220, 'port_type' => 'Seaport',
            'status' => 'Active', 'risk_index' => 120,
        ])->assertRedirect(route('admin.ports.create'))
            ->assertSessionHasErrors(['latitude', 'longitude', 'risk_index']);
    }

    public function test_country_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->admin());
        $payload = [
            'country_name' => 'Test Republic', 'country_code' => 'TR',
            'region' => 'Asia', 'capital' => 'Test City', 'currency' => 'TST',
            'population' => 1000000, 'latitude' => 1.5, 'longitude' => 110.5,
            'risk_index' => 45,
        ];

        $this->post(route('admin.countries.store'), $payload)->assertRedirect(route('countries.index'));
        $country = Country::where('country_code', 'TR')->firstOrFail();
        $this->assertSame('Medium', $country->risk_level);
        $this->get(route('countries.show', $country))->assertOk()->assertSee('45');

        $this->put(route('admin.countries.update', $country), [...$payload, 'country_name' => 'Updated Republic'])
            ->assertRedirect(route('countries.index'));
        $this->assertSame('Updated Republic', $country->fresh()->country_name);

        $this->delete(route('admin.countries.destroy', $country))->assertRedirect(route('countries.index'));
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }

    public function test_country_directory_uses_professional_orbital_background(): void
    {
        $this->actingAs($this->user());
        $this->assertFileExists(public_path('images/country-intelligence-background-v2.png'));

        $this->get(route('countries.index'))
            ->assertOk()
            ->assertSee('country-intelligence-background-v2.png', false)
            ->assertSee('Country intelligence orbital background', false);
    }

    public function test_internal_api_endpoints_return_successful_json(): void
    {
        foreach (['/api/countries', '/api/ports', '/api/risk', '/api/news', '/api/currency', '/api/overview'] as $uri) {
            $this->getJson($uri)->assertOk()->assertJsonPath('status', 'success');
        }
    }

    public function test_realtime_overview_exposes_dashboard_metrics_without_http_caching(): void
    {
        Country::create([
            'country_name' => 'Indonesia',
            'country_code' => 'ID',
            'risk_index' => 72,
        ]);

        $response = $this->getJson('/api/overview');

        $response->assertOk()
            ->assertJsonPath('data.countries', 1)
            ->assertJsonPath('data.high_risk', 1)
            ->assertJsonStructure(['data' => ['updated_at']]);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_currency_trend_uses_historical_provider_values(): void
    {
        Cache::flush();
        $trend = app(TrendDataService::class)->currency('USD', 'IDR');

        $this->assertSame('2026-07-20', $trend[0]['date']);
        $this->assertSame(16225.75, $trend[1]['value']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.frankfurter.dev/v2/rates'));
    }

    public function test_economy_dashboard_covers_all_countries_in_the_dataset(): void
    {
        $this->actingAs($this->user());
        foreach (range(1, 30) as $index) {
            Country::create([
                'country_name' => "Economy $index",
                'country_code' => 'X'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'region' => 'Test Region',
            ]);
        }

        $this->get(route('economy.index'))
            ->assertOk()
            ->assertSee('30 countries available')
            ->assertSee('Economy 1')
            ->assertSee('Economy 30')
            ->assertSee('Exports')
            ->assertSee('Imports')
            ->assertSee('https://flagcdn.com/w80/', false);

        Http::assertSentCount(4);
        foreach (['NY.GDP.MKTP.CD', 'FP.CPI.TOTL.ZG', 'NE.EXP.GNFS.CD', 'NE.IMP.GNFS.CD'] as $indicator) {
            Http::assertSent(fn ($request) => str_contains($request->url(), $indicator));
        }
    }

    public function test_external_weather_failure_keeps_directory_and_map_available(): void
    {
        $this->actingAs($this->user());
        Cache::flush();
        Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID', 'latitude' => -6.2, 'longitude' => 106.8]);
        $factory = new HttpFactory;
        $factory->fake(fn () => throw new ConnectionException('Weather provider offline'));
        Http::swap($factory);

        $this->get(route('weather.index'))->assertOk()->assertSee('Country Weather Intelligence');
        $this->get(route('weather.map'))->assertOk()->assertSee('Global Weather Operations Map');
    }

    public function test_world_bank_failure_keeps_all_database_countries_visible(): void
    {
        $this->actingAs($this->user());
        Cache::flush();
        Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID', 'region' => 'Asia']);
        $factory = new HttpFactory;
        $factory->fake(fn () => throw new ConnectionException('World Bank offline'));
        Http::swap($factory);

        $this->get(route('economy.index'))
            ->assertOk()
            ->assertSee('Indonesia')
            ->assertSee('Historical GDP data unavailable.');
    }

    public function test_news_failure_uses_persisted_cache(): void
    {
        $this->actingAs($this->user());
        Cache::flush();
        config(['services.newsapi.key' => 'test-key']);
        NewsCache::create([
            'keyword' => 'supply chain',
            'title' => 'Cached logistics intelligence',
            'url' => 'https://example.test/cached-news',
            'sentiment' => 'Neutral',
            'published_at' => now(),
        ]);
        $factory = new HttpFactory;
        $factory->fake(fn () => throw new ConnectionException('News provider offline'));
        Http::swap($factory);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Cached logistics intelligence');
    }

    public function test_admin_can_update_and_delete_users_and_articles(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $user = User::factory()->create(['role' => 'Viewer', 'department' => 'Operations']);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Updated Viewer',
            'email' => 'updated-viewer@example.test',
            'role' => 'Analyst',
            'department' => 'Risk',
            'password' => '',
        ])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Viewer', 'role' => 'Analyst']);

        $article = Article::create([
            'title' => 'Initial brief',
            'category' => 'Logistics',
            'content' => 'Initial content',
            'author' => $admin->name,
        ]);
        $this->put(route('admin.articles.update', $article), [
            'title' => 'Updated brief',
            'category' => 'Trade',
            'content' => 'Updated content',
        ])->assertRedirect();
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Updated brief']);

        $this->delete(route('admin.articles.destroy', $article))->assertRedirect();
        $this->delete(route('admin.users.destroy', $user))->assertRedirect();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_risk_trend_is_calculated_from_persisted_snapshots(): void
    {
        $country = Country::create([
            'country_name' => 'Indonesia', 'country_code' => 'ID',
            'risk_index' => 45, 'risk_level' => 'Medium',
        ]);
        foreach ([40, 50] as $score) {
            RiskScore::create([
                'country_id' => $country->id, 'weather_risk' => 40,
                'inflation_risk' => 50, 'news_risk' => 50,
                'currency_risk' => 30, 'total_score' => $score,
                'risk_level' => 'Medium',
            ]);
        }

        $trend = app(TrendDataService::class)->risk($country->id);

        $this->assertCount(1, $trend);
        $this->assertSame(45.0, $trend[0]['value']);
        $this->assertSame(2, $trend[0]['samples']);
    }

    public function test_risk_update_keeps_one_snapshot_per_country_per_day(): void
    {
        $country = Country::create([
            'country_name' => 'Indonesia', 'country_code' => 'ID',
            'currency' => 'IDR', 'latitude' => -6.2, 'longitude' => 106.8,
            'risk_index' => 45, 'risk_level' => 'Medium',
        ]);

        $this->artisan('risk:update')->assertSuccessful();
        $this->artisan('risk:update')->assertSuccessful();

        $this->assertSame(1, RiskScore::where('country_id', $country->id)->count());
        $this->assertDatabaseHas('risk_scores', [
            'country_id' => $country->id,
            'snapshot_date' => now()->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_global_weather_data_classifies_real_weather_layers(): void
    {
        Cache::flush();
        Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID', 'latitude' => -6.2, 'longitude' => 106.8]);
        Country::create(['country_name' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.6, 'longitude' => 139.6]);

        $points = app(WeatherService::class)->globalConditions(Country::all());

        $this->assertCount(2, $points);
        $this->assertTrue($points[0]['rain']);
        $this->assertTrue($points[0]['storm']);
        $this->assertTrue($points[0]['strong_wind']);
    }

    public function test_weather_dashboard_provides_searchable_country_conditions(): void
    {
        $this->actingAs($this->user());
        Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID', 'capital' => 'Jakarta', 'latitude' => -6.2, 'longitude' => 106.8]);
        Country::create(['country_name' => 'Japan', 'country_code' => 'JP', 'capital' => 'Tokyo', 'latitude' => 35.6, 'longitude' => 139.6]);

        $this->get(route('weather.index'))
            ->assertOk()
            ->assertSee('Country Weather Intelligence')
            ->assertSee('ALL COUNTRIES')
            ->assertSee('Current Conditions')
            ->assertSee('Indonesia')
            ->assertSee('Japan')
            ->assertSee('id="hubSearch"', false)
            ->assertSee('id="loadMoreCountries"', false)
            ->assertSee('https://flagcdn.com/w80/', false)
            ->assertDontSee('id="globalWeatherMap"', false);
    }

    public function test_weighted_risk_scoring_uses_required_component_weights(): void
    {
        $result = app(RiskScoreService::class)->calculate([
            'weather_risk' => 30, 'inflation' => 2,
            'news_sentiment' => 'negative', 'currency_risk' => 10,
        ]);

        $this->assertSame(54.0, $result['score']);
        $this->assertSame('Medium', $result['level']);
        $this->assertSame(['weather' => 30.0, 'inflation' => 20.0, 'news' => 100, 'currency' => 10.0], $result['components']);
    }

    public function test_report_center_and_printable_operational_reports_are_available(): void
    {
        $this->actingAs($this->user());
        Country::create([
            'country_name' => 'Indonesia',
            'country_code' => 'ID',
            'region' => 'Asia',
            'risk_index' => 75,
            'risk_level' => 'High',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Intelligence Report Center')
            ->assertSee('Global Risk Report');

        foreach (['executive', 'risk', 'economy', 'ports', 'news', 'watchlist'] as $type) {
            $this->get(route('reports.print', $type))
                ->assertOk()
                ->assertSee('Print / Save as PDF');
        }

        $this->get(route('reports.print', 'users'))->assertForbidden();
        $this->actingAs($this->admin());
        $this->get(route('reports.print', 'users'))->assertOk()->assertSee('Identity & Access Report');
        $this->get(route('reports.print', 'articles'))->assertOk()->assertSee('Intelligence Brief Report');
    }

    public function test_lexicon_sentiment_analysis_counts_words_and_classifies_text(): void
    {
        $result = app(SentimentAnalysisService::class)->analyze('Growth improved, but war caused delay and disruption.');

        $this->assertSame(2, $result['positive_score']);
        $this->assertSame(3, $result['negative_score']);
        $this->assertSame('Negative', $result['sentiment']);
    }

    public function test_lexicon_sentiment_counts_every_repeated_occurrence(): void
    {
        $result = app(SentimentAnalysisService::class)->analyze('war war war growth');

        $this->assertSame(1, $result['positive_score']);
        $this->assertSame(3, $result['negative_score']);
        $this->assertSame('Negative', $result['sentiment']);
    }

    public function test_country_news_risk_only_uses_articles_that_mention_the_country(): void
    {
        $indonesia = Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID']);
        $articles = collect([
            new NewsCache(['keyword' => 'Indonesia', 'title' => 'Indonesia growth remains stable', 'description' => 'Strong recovery']),
            new NewsCache(['keyword' => 'Germany', 'title' => 'Germany war disruption', 'description' => 'Crisis and delay']),
        ]);

        $result = app(CountryNewsRiskService::class)->analyze($indonesia, $articles);

        $this->assertSame(1, $result['article_count']);
        $this->assertSame('Positive', $result['sentiment']);
    }

    public function test_watchlist_supports_ajax_and_enforces_session_ownership(): void
    {
        $this->actingAs($this->user());
        $this->withSession(['watchlist_session_started' => true]);
        $country = Country::create(['country_name' => 'Indonesia', 'country_code' => 'ID']);
        $response = $this->postJson(route('watchlists.store', $country));
        $response->assertCreated()->assertJsonPath('status', 'success');
        $watchlist = Watchlist::firstOrFail();

        $this->deleteJson(route('watchlists.destroy', $watchlist))
            ->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('watchlists', ['id' => $watchlist->id]);

        $foreign = Watchlist::create(['country_id' => $country->id, 'session_id' => 'another-session']);
        $this->deleteJson(route('watchlists.destroy', $foreign))->assertForbidden();
    }

    public function test_country_and_port_mutations_require_admin_authorization(): void
    {
        $this->get(route('admin.countries.create'))->assertRedirect(route('login'));
        $this->post(route('admin.countries.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.ports.index'))->assertRedirect(route('login'));

        $user = User::create(['name' => 'Viewer', 'email' => 'viewer@test.local', 'password' => 'secret-password', 'role' => 'User']);
        $this->actingAs($user);
        $this->get(route('admin.countries.create'))->assertForbidden();
        $this->get(route('admin.ports.index'))->assertForbidden();

        $this->actingAs($this->admin('second-admin@test.local'));
        $this->get(route('admin.countries.create'))->assertOk();
        $this->get(route('admin.ports.index'))->assertOk()->assertSee('Port Dataset Management');
    }

    public function test_currency_api_returns_service_unavailable_when_provider_fails(): void
    {
        $factory = new HttpFactory;
        $factory->fake(fn () => throw new ConnectionException('Provider offline'));
        Http::swap($factory);

        $this->getJson('/api/currency?base=USD')
            ->assertStatus(503)
            ->assertJsonPath('status', 'error');
    }

    public function test_geospatial_runtime_exists_only_on_map_pages(): void
    {
        $this->actingAs($this->user());
        foreach (['/', '/countries', '/ports', '/shipping-routes'] as $uri) {
            $this->get($uri)->assertOk()->assertDontSee('leaflet.js', false);
        }

        $this->get('/map')->assertOk()
            ->assertSee('leaflet.js', false)
            ->assertSee('id="unifiedMap"', false)
            ->assertSee('country-flag-marker', false)
            ->assertSee('country-popup-flag', false);
        $this->get('/weather')->assertOk()
            ->assertDontSee('leaflet.js', false)
            ->assertDontSee('id="globalWeatherMap"', false);
        $this->get('/weather-map')->assertOk()
            ->assertSee('leaflet.js', false)
            ->assertSee('id="globalWeatherMap"', false)
            ->assertSee('data-weather-layer="rain"', false)
            ->assertSee('data-weather-layer="storm"', false)
            ->assertSee('data-weather-layer="wind"', false)
            ->assertSee('api.rainviewer.com/public/weather-maps.json', false)
            ->assertSee('Radar by RainViewer')
            ->assertSee('id="weatherCountryFilter"', false);
    }

    public function test_legacy_adminlte_and_bootstrap_four_assets_are_removed(): void
    {
        $this->actingAs($this->user());
        $this->assertFileDoesNotExist(public_path('vendor/adminlte'));
        $this->assertFileDoesNotExist(public_path('vendor/bootstrap'));
        $this->assertFileDoesNotExist(resource_path('views/vendor/adminlte/page.blade.php'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('width=device-width, initial-scale=1', false)
            ->assertDontSee('adminlte', false);
    }

    public function test_admin_routes_are_unique_and_available(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret-password', 'role' => 'Admin', 'department' => 'IT']);
        $this->actingAs($admin);
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/articles')->assertOk();
        $this->get('/admin/settings')->assertOk();
        $this->get('/admin/ports')->assertOk();
    }

    public function test_admin_login_rejects_invalid_credentials_and_accepts_admin(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret-password', 'role' => 'Admin']);
        $this->post(route('login.store'), ['email' => 'admin@test.local', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => 'admin@test.local', 'password' => 'secret-password'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertAuthenticated();
    }

    public function test_guests_are_redirected_to_login_before_opening_the_platform(): void
    {
        foreach (['/', '/countries', '/ports', '/economy', '/news', '/risk-score', '/reports', '/map'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }

        $this->get(route('login'))->assertOk()->assertSee('Create an account');
        $this->get(route('register'))->assertOk()->assertSee('Create user account');
    }

    public function test_public_registration_creates_an_active_standard_user(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Platform User',
            'email' => 'new.user@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $user = User::where('email', 'new.user@example.com')->firstOrFail();
        $this->assertSame('User', $user->role);
        $this->assertSame('Active', $user->status);
        $this->assertNotNull($user->last_login_at);
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_non_admin_user_can_login_but_cannot_access_admin_routes(): void
    {
        User::create([
            'name' => 'Supply Chain Analyst',
            'email' => 'analyst@test.local',
            'password' => 'secret-password',
            'role' => 'Analyst',
        ]);

        $this->post(route('login.store'), [
            'email' => 'analyst@test.local',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_inactive_user_cannot_login_and_successful_login_is_tracked(): void
    {
        $inactive = User::create([
            'name' => 'Inactive User', 'email' => 'inactive@test.local',
            'password' => 'secret-password', 'role' => 'Viewer', 'status' => 'Inactive',
        ]);
        $this->post(route('login.store'), ['email' => $inactive->email, 'password' => 'secret-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $active = User::create([
            'name' => 'Active User', 'email' => 'active@test.local',
            'password' => 'secret-password', 'role' => 'Viewer', 'status' => 'Active',
        ]);
        $this->post(route('login.store'), ['email' => $active->email, 'password' => 'secret-password'])
            ->assertRedirect(route('dashboard'));
        $this->assertNotNull($active->fresh()->last_login_at);
    }

    public function test_report_csv_export_is_available_and_restricted_reports_require_admin(): void
    {
        $this->actingAs($this->user());
        $this->get(route('reports.export', 'risk'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get(route('reports.export', 'users'))->assertForbidden();

        $this->actingAs($this->admin());
        $this->get(route('reports.export', 'users'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_display_language_can_be_changed_and_is_persisted_in_session(): void
    {
        $this->actingAs($this->user());
        $this->from(route('dashboard'))
            ->post(route('language.update', 'en'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('locale', 'en');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Countries')
            ->assertSee('Shipping Routes')
            ->assertSee('Language');

        $this->post(route('language.update', 'ja'))
            ->assertRedirect()
            ->assertSessionHas('locale', 'ja')
            ->assertCookie('googtrans');
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('translate.google.com/translate_a/element.js', false)
            ->assertSee('lang="ja"', false);

        $this->post(route('language.update', 'unsupported'))->assertNotFound();
    }

    private function admin(string $email = 'admin@test.local'): User
    {
        return User::firstOrCreate(['email' => $email], [
            'name' => 'Admin', 'password' => 'secret-password',
            'role' => 'Admin', 'department' => 'IT',
        ]);
    }

    private function user(string $email = 'user@test.local'): User
    {
        return User::firstOrCreate(['email' => $email], [
            'name' => 'Platform User', 'password' => 'Secret-password1',
            'role' => 'User', 'department' => 'Operations', 'status' => 'Active',
        ]);
    }
}
