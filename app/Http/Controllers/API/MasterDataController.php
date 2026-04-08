<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MasterAspect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index()
    {
        $aspects = MasterAspect::with('indicators.parameters.factors.subFactors')->get();

        $formattedData = $aspects->map(function ($aspect) {
            return [
                'id' => (string) $aspect->id,
                'name' => $aspect->name,
                'bobot' => (float) $aspect->bobot,
                'is_modifier' => (bool) $aspect->is_modifier, // 🆕 TAMBAH INI
                'indicators' => $aspect->indicators->map(function ($indicator) {
                    return [
                        'id' => (string) $indicator->id,
                        'name' => $indicator->name,
                        'bobot' => (float) $indicator->bobot,
                        'parameters' => $indicator->parameters->map(function ($parameter) {
                            return [
                                'id' => (string) $parameter->id,
                                'name' => $parameter->name,
                                'bobot' => (float) $parameter->bobot,
                                'factors' => $parameter->factors->map(function ($factor) {
                                    return [
                                        'id' => (string) $factor->id,
                                        'name' => $factor->name,
                                        'subFactors' => $factor->subFactors->map(function ($sub) {
                                            return [
                                                'id' => (string) $sub->id,
                                                'name' => $sub->name,
                                                'description' => $sub->description
                                            ];
                                        })->values()->all()
                                    ];
                                })->values()->all()
                            ];
                        })->values()->all()
                    ];
                })->values()->all()
            ];
        });

        return response()->json($formattedData);
    }

    public function sync(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->json()->all();
            $aspectIdsToKeep = [];

            foreach ($data as $aspectData) {
                $aspectId = is_numeric($aspectData['id']) ? $aspectData['id'] : null;

                $aspect = MasterAspect::updateOrCreate(
                    ['id' => $aspectId],
                    [
                        'name' => $aspectData['name'],
                        'bobot' => $aspectData['bobot'] ?? 0,
                        'is_modifier' => $aspectData['is_modifier'] ?? false // 🆕 TAMBAH INI BUAT NYIMPEN
                    ]
                );
                $aspectIdsToKeep[] = $aspect->id;

                $indicatorIdsToKeep = [];
                if (!empty($aspectData['indicators'])) {
                    foreach ($aspectData['indicators'] as $indData) {
                        $indId = is_numeric($indData['id']) ? $indData['id'] : null;
                        $indicator = $aspect->indicators()->updateOrCreate(
                            ['id' => $indId],
                            ['name' => $indData['name'], 'bobot' => $indData['bobot'] ?? 0]
                        );
                        $indicatorIdsToKeep[] = $indicator->id;

                        $paramIdsToKeep = [];
                        if (!empty($indData['parameters'])) {
                            foreach ($indData['parameters'] as $paramData) {
                                $paramId = is_numeric($paramData['id']) ? $paramData['id'] : null;
                                $parameter = $indicator->parameters()->updateOrCreate(
                                    ['id' => $paramId],
                                    ['name' => $paramData['name'], 'bobot' => $paramData['bobot'] ?? 0]
                                );
                                $paramIdsToKeep[] = $parameter->id;

                                $factorIdsToKeep = [];
                                if (!empty($paramData['factors'])) {
                                    foreach ($paramData['factors'] as $factorData) {
                                        $factorId = is_numeric($factorData['id']) ? $factorData['id'] : null;
                                        $factor = $parameter->factors()->updateOrCreate(
                                            ['id' => $factorId],
                                            ['name' => $factorData['name']]
                                        );
                                        $factorIdsToKeep[] = $factor->id;

                                        $subIdsToKeep = [];
                                        if (!empty($factorData['subFactors'])) {
                                            foreach ($factorData['subFactors'] as $subData) {
                                                $subId = is_numeric($subData['id']) ? $subData['id'] : null;
                                                $subFactor = $factor->subFactors()->updateOrCreate(
                                                    ['id' => $subId],
                                                    ['name' => $subData['name'], 'description' => $subData['description'] ?? null]
                                                );
                                                $subIdsToKeep[] = $subFactor->id;
                                            }
                                        }
                                        $factor->subFactors()->whereNotIn('id', $subIdsToKeep)->delete();
                                    }
                                }
                                $parameter->factors()->whereNotIn('id', $factorIdsToKeep)->delete();
                            }
                        }
                        $indicator->parameters()->whereNotIn('id', $paramIdsToKeep)->delete();
                    }
                }
                $aspect->indicators()->whereNotIn('id', $indicatorIdsToKeep)->delete();
            }
            MasterAspect::whereNotIn('id', $aspectIdsToKeep)->delete();

            DB::commit();

            return $this->index();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
