<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\Collection;
use App\Models\CardCollection;
use App\Models\LotOnSale;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CardsController extends Controller
{
    //
    public function registerCard(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'name' => ['required', 'max:50'],
            'description' => ['required', 'max:500'],
            'collection' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            //Generar el nueva carta

            $data = $req->getContent();
            $data = json_decode($data);

            $collection = Collection::where(
                'id',
                '=',
                $data->collection
            )->first();

            if ($collection) {
                $card = new Card();
                //$newCollection = new Collection();
                $card->name = $data->name;
                $card->description = $data->description;

                try {
                    $card->save();
                    $cardCollection = new CardCollection();
                    $cardCollection->card_id = $card->id;
                    $cardCollection->collection_id = $collection->id;
                    $cardCollection->save();
                    $respuesta['msg'] =
                        'Carta guardada con id ' .
                        $card->id .
                        ' y cardCollection guardado con el id ' .
                        $cardCollection->id;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] =
                        'Se ha producido un error: ' . $e->getMessage();
                }
            } else {
                $respuesta['status'] = 0;
                $respuesta['msg'] = 'La coleccion ingresada no existe';
            }
        }
        return response()->json($respuesta);
    }

    public function registerCollection(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'name' => ['required', 'max:50'],
            'symbol' => ['required', 'max:100'],
            'launch_date' => ['required', 'date'],
            'cards' => ['required']
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            //Generar el nueva carta

            $data = $req->getContent();
            $data = json_decode($data);
            //$i=0;
            //$j=0;
            $validId =[];
            foreach ($data->cards as $addCard) {
                if(isset($addCard->id)){
               // $i++;
                $card = Card::where('id','=',$addCard->id)->first();
                if($card){
                    //$j++;
                    array_push($validId,$card->id);
                   
                }


                }elseif (
                            isset($addCard->name) &&
                            isset($addCard->description) 
                        ) {
                            
                            $newCard = new Card();
                            $newCard->name = $addCard->name;
                            $newCard->description = $addCard->description;

                            try {
                                $newCard->save();
                                array_push($validId,$newCard->id);
                                $respuesta['msg'] ='Carta guardada con id ' .$newcard->id;
                                    
                            } catch (\Exception $e) {
                                $respuesta['status'] = 0;
                                $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
                            }



            }else{
                $respuesta['status'] = 0;
                $respuesta['msg'] ='Los datos ingresados no corresponden a los parametros de carta';
            }
            
        }

        //print_r ($validId);
        if(!empty($validId)){
            $cardsIds = implode (", ",$validId); 
            try{
            $collection = new Collection();
            $collection->name = $data->name;
            $collection->symbol = $data->symbol;
            $collection->launch_date = $data->launch_date;
            $collection->save();

            foreach($validId as $id){
                $cardCollection = new CardCollection();
                $cardCollection->card_id = $id;
                $cardCollection->collection_id = $collection->id;
                $cardCollection->save();
            }
            $respuesta['msg'] ='Se ha creado la cardCollection con id: '.$cardCollection->id .' y se le han agregado las carta(s) con id(s): '.$cardsIds;
            
        }catch (\Exception $e) {
            $respuesta['status'] = 0;
            $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
        }
        }
        //$respuesta['msg'] = "hay ".$i." ids y existen ".$j;

        
    }
    return response()->json($respuesta);
}

public function cardSearcher(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'search' => ['required', 'max:50']
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {

        try{
            $data = $req->getContent();
            $data = json_decode($data);

            $search = DB::table('cards')
                        ->select(['id','name','description'])
                        ->where('name','like','%'. $data -> search.'%')
                        ->get();
            $respuesta['msg'] = $search;
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
        }
    }
    return response()->json($respuesta);
}


public function putOnSale(Request $req){
        $respuesta = ['status' => 1, 'msg' => ''];
        
        $validator = Validator::make(json_decode($req->getContent(), true),
         [
            'card_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
            'total_price' => ['required', 'numeric','min:0','not_in:0'],

        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            $data = $req->getContent();
            $data = json_decode($data);

            $card = Card::where(
                'id',
                '=',
                $data->card_id
            )->first();

            if($card){
                $lot = new LotOnSale();
                $lot->card_id = $data->card_id;
                $lot->quantity = $data->quantity;
                $lot->total_price = $data->total_price;
                $lot->seller_id = $req->user->id ;
                try {
                    $lot->save();
                    $respuesta['msg'] =
                        'Lote guardado con id ' .
                        $lot->id;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] =
                        'Se ha producido un error: ' . $e->getMessage();
                }

            }else{
                $respuesta['msg'] =
                        'La carta que intenta vender no esta registrada, busque el id correcto';
                
            }
    }
    return response()->json($respuesta);
}

public function cardsOnSale(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'search' => ['required', 'max:50']
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {

        try{
            $data = $req->getContent();
            $data = json_decode($data);

            $search = DB::table('cards_sale')
                        ->join('users', 'users.id', '=', 'cards_sale.seller_id')
                        ->join('cards', 'cards.id', '=', 'cards_sale.card_id')
                        ->select('cards.name', 'cards_sale.quantity', 'cards_sale.total_price', 'users.username as seller'  )
                        ->where('cards.name','like','%'. $data -> search.'%')
                        ->orderBy('cards_sale.total_price','ASC')
                        ->get();
            $respuesta['msg'] = $search;
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
        }
    }
    return response()->json($respuesta);
}



}
