<?php
declare(strict_types=1);
namespace App\Modules\Analysis\Presentation\Http\Controllers;
use App\Modules\Analysis\Application\Services\ManufacturingAnalyticsService;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class ManufacturingReportController
{
    public function __construct(private readonly ManufacturingAnalyticsService $service) {}
    public function show(Request $r, string $type): JsonResponse { $filters=$r->only(['date_from','date_to','product_id','production_order_id','work_center_id','production_resource_id','operator_id']);$data=match($type){'efficiency','planned-vs-real'=>$this->service->efficiency($filters),'oee'=>$this->service->oee($filters),'standard-times'=>$this->service->standardTimeEvidence($filters,(int)$r->integer('minimum_sample',5)),default=>$this->service->overview($filters)};return ApiResponse::success(['report_type'=>$type,'generated_at'=>now()->toIso8601String(),'filters'=>$filters,'data'=>$data], 'Manufacturing report'); }
    public function export(Request $r, string $type) { $response=$this->show($r,$type)->getData(true);$rows=$response['data']['data']['data']??$response['data']['data']['facts']??[];$headers=['dimension','key','count','efficiency_percent','availability_percent','performance_percent','quality_percent','oee_percent'];$csv=fopen('php://temp','r+');fputcsv($csv,$headers);foreach($rows as $row){fputcsv($csv,array_map(fn($h)=>is_array($row[$h]??null)?json_encode($row[$h]):($row[$h]??null),$headers));}rewind($csv);$content=stream_get_contents($csv);fclose($csv);return response($content,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>'attachment; filename="manufacturing-'.$type.'.csv"']); }
}
