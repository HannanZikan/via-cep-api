<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CepResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CepController extends Controller
{
    public function getAddressByCep(string $ceps): JsonResponse
    {
        $validatedCeps = $this->validateCep($ceps);

        if ($validatedCeps->status() != 200) {
            return $validatedCeps;
        }

        foreach ($validatedCeps->getData() as $cep) {
            $address[] = Http::get('https://viacep.com.br/ws/' . $cep . '/json/')->json();
        }

        return response()->json(CepResource::collection(collect($address)->sortByDesc('cep')));
    }

    public function validateCep(string $ceps): JsonResponse
    {
        $arrayCeps    = explode(',', $ceps);
        $validatedCep = [];
        foreach ($arrayCeps as $cep) {
            if (!preg_match('/^[0-9]{5,5}([- ]?[0-9]{3,3})?$/', $cep)) {
                return response()->json(['message' => 'O Cep ' . $cep . ' é inválido! Favor verificar a formatação e tentar novamente'], 422);
            }
            $validatedCep[] = Str::replace('-', '', $cep);
        }

        return response()->json($validatedCep, 200);
    }
}
