<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\TrendDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateController extends Controller
{
    private const MAJOR = [
        'USD'=>['name'=>'US Dollar','flag'=>'🇺🇸','flag_code'=>'us'], 'EUR'=>['name'=>'Euro','flag'=>'🇪🇺','flag_code'=>'eu'],
        'IDR'=>['name'=>'Indonesian Rupiah','flag'=>'🇮🇩','flag_code'=>'id'], 'JPY'=>['name'=>'Japanese Yen','flag'=>'🇯🇵','flag_code'=>'jp'],
        'CNY'=>['name'=>'Chinese Yuan','flag'=>'🇨🇳','flag_code'=>'cn'], 'SGD'=>['name'=>'Singapore Dollar','flag'=>'🇸🇬','flag_code'=>'sg'],
        'GBP'=>['name'=>'British Pound','flag'=>'🇬🇧','flag_code'=>'gb'], 'AUD'=>['name'=>'Australian Dollar','flag'=>'🇦🇺','flag_code'=>'au'],
        'KRW'=>['name'=>'South Korean Won','flag'=>'🇰🇷','flag_code'=>'kr'], 'MYR'=>['name'=>'Malaysian Ringgit','flag'=>'🇲🇾','flag_code'=>'my'],
    ];

    public function index(Request $request, TrendDataService $trends)
    {
        $base=strtoupper((string)$request->input('base','USD'));
        if (!array_key_exists($base,self::MAJOR)) $base='USD';
        $exchange=Cache::remember("exchange.$base",now()->addHour(),function() use($base){
            try {$response=Http::timeout(15)->get("https://open.er-api.com/v6/latest/$base");return $response->successful()?$response->json():null;}catch(\Throwable){return null;}
        });
        $rates=$exchange['rates']??[];
        $currencyFlags=Country::whereNotNull('currency')->whereNotNull('flag')
            ->orderBy('country_name')->get(['currency','flag'])
            ->groupBy('currency')->map(fn($countries)=>$countries->first()->flag);
        $currencyFlags=collect($currencyFlags->all())->merge([
            'EUR'=>'🇪🇺','XAF'=>'🌍','XOF'=>'🌍','XCD'=>'🌎','XPF'=>'🌏',
            'XDR'=>'🏦','XAU'=>'🟡','XAG'=>'⚪','BTC'=>'₿',
        ]);
        $currencyFlagCodes=Country::whereNotNull('currency')->whereNotNull('country_code')
            ->orderByDesc('population')->get(['currency','country_code'])
            ->groupBy('currency')->map(fn($countries)=>strtolower($countries->first()->country_code));
        $majorRates=collect(self::MAJOR)->map(fn($meta,$code)=>['code'=>$code,...$meta,'rate'=>$rates[$code]??null])->values();
        $allRates=collect($rates)->map(fn($rate,$code)=>[
            'code'=>$code,
            'rate'=>$rate,
            'name'=>self::MAJOR[$code]['name']??$this->currencyName($code),
            'flag'=>self::MAJOR[$code]['flag']??$currencyFlags->get($code,'🌐'),
            'flag_code'=>self::MAJOR[$code]['flag_code']??$currencyFlagCodes->get($code),
        ])->values();

        $quote = strtoupper((string) $request->input('quote', $base === 'IDR' ? 'USD' : 'IDR'));
        if (! isset($rates[$quote]) || $quote === $base) {
            $quote = $base === 'USD' ? 'IDR' : 'USD';
        }
        $currencyTrend = $trends->currency($base, $quote);

        return view('exchange.index',compact('exchange','base','quote','currencyTrend','majorRates','allRates'));
    }

    private function currencyName(string $code): string
    {
        return $code;
    }
}
