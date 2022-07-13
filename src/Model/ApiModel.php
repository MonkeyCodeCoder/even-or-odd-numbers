
<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 04/01/2018
 * Time: 14:03
 */

namespace App\Model;

use Symfony\Component\HttpFoundation\Response;


class ApiModel
{
    const URl="http://localhost:8545";
    const ACCOUNT="0xDd421A95ab8D53919092Cf2A144815905C2BC4Db";
    const CONTRACT="0x690Ea531A7ba08BEA5789BB0f708E73CCe864276";

    function getDocumentCount(){
        // keccak-256 de getDocumentCount() es 3d1c227335f9755b3b49b8845a25fff553fbe76676aff139dcdcb6ac8783f91c, se toman los 8 primeros caracteres
        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> "0x3d1c2273"],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000004  la respuesta en hex
        $argResult = substr($result,2);// eliminar 0x
        return hexdec($argResult);


    }

    function getDocumentAtIndex($index){
        //index plezplasado 32 bytes
        $indexPad =str_pad($index, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getDocumentAtIndex(uint256)77d2ab4fabe09035e251da8807814748a7110687787881ee10e31bb505b9d395, se toman los 8 primeros caracteres
        $call="0x77d2ab4f". $indexPad;


        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> $call],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000020 indica donde empieza la definicion de la respuesta(a los 32 B)
        //0000000000000000000000000000000000000000000000000000000000000040 indica el tamaño de la respuesta(0x40-> 64 B -> 128 caracteres)
        //6636343436613831616566613436613165323537383535663262623935356563 la respuesta en hex
        //3037313830616439366334376263383562386661663236353662393138343539

        $lenghtAndArgResult = substr($result,66);// string con la longitud de la respuesta y la respuesta
        $lenghtResult= substr($lenghtAndArgResult,0,64);// logitud del resultado
        $argResult=substr($lenghtAndArgResult,64,hexdec($lenghtResult)*2); // argumento

        return $this->Hex2String($argResult);
    }

    function getDocumentList(){
        $count =$this->getDocumentCount();
        for($i=0;$i<$count;$i++){
            $result[$i]= $this->getDocumentAtIndex($i);
        }
        return \Graze\GuzzleHttp\JsonRpc\json_encode($result );
    }

    function exists($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de exists(string)261a323e87a367a6fec01842ab1be2786193d1a5558fde3e4834378f2761ad3a, se toman los 8 primeros caracteres
        $call="0x261a323e". $argIdPos . $lengthIdHex . $idHex;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> $call],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000000
        //o
        //0000000000000000000000000000000000000000000000000000000000000001
        $argResult = substr($result,65);// string con la longitud de la respuesta y la respuesta

        return $argResult;

    }

//----------------------------------------------------getters------------------------------------------
    function getInvoiceNumber($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getInvoiceNumber(string) 0b58d080e1defde665f3203704d8e49229d00557e10cd9b8c6fdb8cb3aba74b6, se toman los 8 primeros caracteres
        $call="0x0b58d080". $argIdPos . $lengthIdHex . $idHex;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> $call],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000020  indica donde empieza la definicion de la respuesta(a los 32 B)
        //0000000000000000000000000000000000000000000000000000000000000009  indica el tamaño de la respuesta(9 B -> 18 caracteres)
        //3031372d30303535330000000000000000000000000000000000000000000000  la respuesta en hex
        $lenghtAndArgResult = substr($result,66);// string con la longitud de la respuesta y la respuesta
        $lenghtResult= substr($lenghtAndArgResult,0,64);// logitud del resultado
        $argResult=substr($lenghtAndArgResult,64,hexdec($lenghtResult)*2); // argumento

        return $this->Hex2String($argResult);

    }

    function getFiscalYear($id){
        // hex f7446a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459
        //$idhex = "66373434366138316165666134366131653235373835356632626239353565633037313830616439366334376263383562386661663236353662393138343539";
        $idHex = $this->String2Hex($id);
        // tamaño en bytes del hex, 64 bytes -> 40 en hex
        //$leghthex= "0000000000000000000000000000000000000000000000000000000000000040";
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getFiscalYear(string) 08702936ef292a7d8cdb9771680860a168f7280ba759592306236916a440d99e, se toman los 8 primeros caracteres
        $call="0x08702936". $argIdPos . $lengthIdHex . $idHex;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> $call],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000020  indica donde empieza la definicion de la respuesta(a los 32 B)
        //0000000000000000000000000000000000000000000000000000000000000009  indica el tamaño de la respuesta(9 B -> 18 caracteres)
        //3031372d30303535330000000000000000000000000000000000000000000000  la respuesta en hex
        $lenghtAndArgResult = substr($result,66);// string con la longitud de la respuesta y la respuesta
        $lenghtResult= substr($lenghtAndArgResult,0,64);// logitud del resultado
        $argResult=substr($lenghtAndArgResult,64,hexdec($lenghtResult)*2); // argumento

        return $this->Hex2String($argResult);

    }

    function getTotal($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getInvoiceNumber(string) 0b58d080e1defde665f3203704d8e49229d00557e10cd9b8c6fdb8cb3aba74b6, se toman los 8 primeros caracteres
        $call="0x0b58d080". $argIdPos . $lengthIdHex . $idHex;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_call','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"data"=> $call],'latest'
            ],'id'=>67
        ];
        $params= json_encode($data);
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::URl);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handler, CURLOPT_POST,true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($handler);
        curl_close($handler);
        $json=json_decode($response,true);
        $result=$json['result'];
        // ejemplo de resultado
        //0x
        //0000000000000000000000000000000000000000000000000000000000000020  indica donde empieza la definicion de la respuesta(a los 32 B)
        //0000000000000000000000000000000000000000000000000000000000000009  indica el tamaño de la respuesta(9 B -> 18 caracteres)
        //3031372d30303535330000000000000000000000000000000000000000000000  la respuesta en hex
        $lenghtAndArgResult = substr($result,66);// string con la longitud de la respuesta y la respuesta
        $lenghtResult= substr($lenghtAndArgResult,0,64);// logitud del resultado
        $argResult=substr($lenghtAndArgResult,64,hexdec($lenghtResult)*2); // argumento