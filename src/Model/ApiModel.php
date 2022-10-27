
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

        return $this->Hex2String($argResult);

    }

    function getFactoringTotal($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getFactoringTotal(string) a78e902faa5ef78b006227c9216bff9c0815ee4a8a82365f8d20c10487cd6b41, se toman los 8 primeros caracteres
        $call="0xa78e902f". $argIdPos . $lengthIdHex . $idHex;

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

    function getState($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getState(string) e33f77ca62a8a5b72df2cc01fef6cf1993d3636288d0cb3668423c17e165f016, se toman los 8 primeros caracteres
        $call="0xe33f77ca". $argIdPos . $lengthIdHex . $idHex;

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

    function getCurrency($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getCurrency(string) f8066d6b070491baee72a2526e2f28168d81c57d0548751713bd4b5de1688900, se toman los 8 primeros caracteres
        $call="0xf8066d6b". $argIdPos . $lengthIdHex . $idHex;

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

    function getPaymentType($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getPaymentType(string) 87570100868db69edafd58092dbfb8e1e473e0890385c935a38b217ab5b71182, se toman los 8 primeros caracteres
        $call="0x87570100". $argIdPos . $lengthIdHex . $idHex;

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

    function getSupplierName($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getSupplierName(string) 9c72ab0b8d6d82a46cb352f3a53443b91ae149d0707904b6a0355d2f54145680, se toman los 8 primeros caracteres
        $call="0x9c72ab0b". $argIdPos . $lengthIdHex . $idHex;

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

    function getCustomerName($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getCustomerName(string) 100d4d230c6c8262044d860a036155fd815ab017d3aa3444cdbb303598ffcc05, se toman los 8 primeros caracteres
        $call="0x100d4d23". $argIdPos . $lengthIdHex . $idHex;

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

    function getFinancialInstitutionName($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getFinancialInstitutionName(string) 946143a25b2d5ddf413b79af7d18ee479a31d32cccf16457937bd548cc2f02a1, se toman los 8 primeros caracteres
        $call="0x946143a2". $argIdPos . $lengthIdHex . $idHex;

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

    function getFactoringState($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getFactoringState(string) d712c0bf78b679d8a37960c1a83cbcb5a4aac227abe47e21c725aaf6b006a953, se toman los 8 primeros caracteres
        $call="0xd712c0bf". $argIdPos . $lengthIdHex . $idHex;

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

    function getPaymentTerms($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getPaymentTerms(string) 54aed18821336ee39415dcfdcf416a5f88f4c955ca46a47c6631c12b56f2eb75, se toman los 8 primeros caracteres
        $call="0x54aed188". $argIdPos . $lengthIdHex . $idHex;


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

    function getInvoiceDate($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getInvoiceDate(string) 9510eacf26978c3cddb8764681507cb70cf976e88deb33c75716738abfa3aa64, se toman los 8 primeros caracteres
        $call="0x9510eacf". $argIdPos . $lengthIdHex . $idHex;


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

    function getPaymentDate($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getPaymentDate(string) 477f86014c29d9d39b2508ab56b2771744489a60cf960325a77001015799531a, se toman los 8 primeros caracteres
        $call="0x477f8601". $argIdPos . $lengthIdHex . $idHex;

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

    function getExpirationDate($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getExpirationDate(string) cbb9cd5d57a6c7e2204b22b93093b1ceefbd6d55b4220f0b0a3c382032a6d57a, se toman los 8 primeros caracteres
        $call="0xcbb9cd5d". $argIdPos . $lengthIdHex . $idHex;

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

    function getFactoringExpirationDate($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        //32 bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);
        //keccak-256 de getFactoringExpirationDate(string) 8acb1e95bf3571c442fc562adfd5a193438bd78ea731a70ee3a8077eeef351b7, se toman los 8 primeros caracteres
        $call="0x8acb1e95". $argIdPos . $lengthIdHex . $idHex;

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

    function getAll($id){
        $invoiceNumber = $this -> getInvoiceNumber($id);
        $fiscalYear = $this -> getFiscalYear($id);
        $total = $this ->getTotal($id);
        $factoringTotal = $this ->getFactoringTotal($id);
        $state = $this ->getState($id);
        $currency = $this ->getCurrency($id);
        $paymentType = $this ->getPaymentType($id);
        $supplierName = $this ->getSupplierName($id);
        $customerName = $this ->getCustomerName($id);
        $financialInstitutionName = $this ->getFinancialInstitutionName($id);
        $factoringState = $this ->getFactoringState($id);
        $paymentTerms = $this ->getPaymentTerms($id);
        $invoiceDate = $this ->getInvoiceDate($id);
        $paymentDate = $this ->getPaymentDate($id);
        $expirationDate = $this ->getExpirationDate($id);
        $factoringExpirationDate = $this ->getFactoringExpirationDate($id);

        $result="invoiceNumber: ".$invoiceNumber."\n"."fiscalYear: ".$fiscalYear."\n"."total: ".$total."\n"."factoringTotal: ".$factoringTotal.
            "\n"."state: ".$state."\n"."currency: ".$currency."\n"."paymentType: ".$paymentType."\n"."supplierName: ".$supplierName."\n".
            "customerName: ".$customerName."\n"."financialInstitutionName: ".$financialInstitutionName."\n"."factoringState: ".$factoringState.
            "\n"."paymentTerms: ".$paymentTerms."\n"."invoiceDate: ".$invoiceDate."\n"."paymentDate: ".$paymentDate."\n"."expirationDate: "
            .$expirationDate."\n"."factoringExpirationDate: ".$factoringExpirationDate;
        return $result;
    }

//----------------------------------------------------delete--------------------------------------------------
    function deleteDocument($id){
        // hex del id
        $idHex = $this->String2Hex($id);
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex=str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        // bytes desde el id del metodo hasta el argumento, hex de 32 = 20
        $argIdPos =str_pad(20, 64, "0", STR_PAD_LEFT);

        //keccak-256 de deleteDocument(string) 635994f8db12f568c0607e4ea4ed49f862f8f5b344844da19e37321a497576df
        $call="0x635994f8". $argIdPos.$lengthIdHex.$idHex;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"gas"=>"0x927c0","data"=> $call]],'id'=>67
        ];
        $params= json_encode($data);
        $this->unlockAccount();
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
        return "hash de la transferencia : ".$result;
    }

    function deleteAll(){
        //keccak-256 de deleteAll() 4c164407fed0cf6a8ddb375aa41136c1c45789dce48020e0f048ec4ad43a1262
        $call="0x4c164407";
        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"gas"=>"0x927c0","data"=> $call]],'id'=>67
        ];
        $params= json_encode($data);
        $this->unlockAccount();
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
        return "hash de la transferencia : ".$result;
    }

//-----------------------------------------------------insert--------------------------------------------------
    function insertDocument($id, $invoiceNumber,$fiscalYear,$total ,$factoringTotal ,$state ,$currency,$paymentType,
                            $supplierName,$customerName,$financialInstitutionName,$factoringState,$paymentTerms,
                            $invoiceDate,$paymentDate,$expirationDate,$factoringExpirationDate){
        // hex de los parametros
        $idHex = $this->String2Hex($id);
        $invoiceNumberHex = $this->String2Hex($invoiceNumber);
        $fiscalYearHex = $this->String2Hex($fiscalYear);
        $totalHex = $this->String2Hex($total);
        $stateHex = $this->String2Hex($state);
        $currencyHex = $this->String2Hex($currency);
        $paymentTypeHex = $this->String2Hex($paymentType);
        $supplierNameHex = $this->String2Hex($supplierName);
        $customerNameHex = $this->String2Hex($customerName);
        $paymentTermsHex = $this->String2Hex($paymentTerms);
        $invoiceDateHex = $this->String2Hex($invoiceDate);

        // dezplazar los parametros
        $invoiceNumberHexPad = str_pad($invoiceNumberHex, 64, "0");
        $fiscalYearHexPad = str_pad($fiscalYearHex, 64, "0");
        $totalHexPad = str_pad($totalHex, 64, "0");
        $stateHexPad = str_pad($stateHex, 64, "0");
        $currencyHexPad = str_pad($currencyHex, 64, "0");
        $paymentTypeHexPad = str_pad($paymentTypeHex, 64, "0");
        $supplierNameHexPad = str_pad($supplierNameHex, 64, "0");
        $customerNameHexPad = str_pad($customerNameHex, 64, "0");
        $paymentTermsHexPad = str_pad($paymentTermsHex, 64, "0");
        $invoiceDateHexPad = str_pad($invoiceDateHex, 64, "0");

        //obtener el tamaño de los parametros
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthIdHex = str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthInvoiceNumberHex = str_pad(dechex(strlen($invoiceNumberHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthFiscalYearHex = str_pad(dechex(strlen($fiscalYearHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthTotalHex = str_pad(dechex(strlen($totalHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthStateHex = str_pad(dechex(strlen($stateHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthCurrencyHex = str_pad(dechex(strlen($currencyHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthPaymentTypeHex = str_pad(dechex(strlen($paymentTypeHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthSupplierNameHex = str_pad(dechex(strlen($supplierNameHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthCustomerNameHex = str_pad(dechex(strlen($customerNameHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthPaymentTermsHex = str_pad(dechex(strlen($paymentTermsHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthInvoiceDateHex = str_pad(dechex(strlen($invoiceDateHex )/2), 64, "0", STR_PAD_LEFT);

        //posiciones de los parametros
        // bytes desde el id del metodo hasta el argumento, hex de (11*32)=352= 160 hex
        $argIdPos = str_pad("160", 64, "0", STR_PAD_LEFT);
        //(14*32)=448 = 1c0 hex
        $argInvoiceNumberPos = str_pad("1c0", 64, "0", STR_PAD_LEFT);
        //(16*32)=512 = 200 hex
        $argFiscalYearPos = str_pad("200", 64, "0", STR_PAD_LEFT);
        //(18*32)=576 = 240 hex
        $argTotaPos = str_pad("240", 64, "0", STR_PAD_LEFT);
        //(20*32)=640 = 280 hex
        $argStatePos = str_pad("280", 64, "0", STR_PAD_LEFT);
        //(22*32)=704 = 2c0 hex
        $argCurrencyPos = str_pad("2c0", 64, "0", STR_PAD_LEFT);
        //(24*32)=768 = 300 hex
        $argPaymentTypePos = str_pad("300", 64, "0", STR_PAD_LEFT);
        //(26*32)=832 = 340 hex
        $argSupplierNamePos = str_pad("340", 64, "0", STR_PAD_LEFT);
        //(28*32)=896 = 380 hex
        $argCustomerNamePos = str_pad("380", 64, "0", STR_PAD_LEFT);
        //(30*32)=960 = 3c0 hex
        $argPaymentTermsPos = str_pad("3c0", 64, "0", STR_PAD_LEFT);
        //(32*32)=1024 = 400 hex
        $argInvoiceDatePos = str_pad("400", 64, "0", STR_PAD_LEFT);


        //keccak-256 de function insertDocument(string,string,string,string,string,string,string,string,string,string,string)
        //7d3665b889b26a9e4eae5781fb9e2a6c9521643809dd5fe0b51fb71745adb4b8
        $call="0x7d3665b8". $argIdPos.$argInvoiceNumberPos.$argFiscalYearPos.$argTotaPos.$argStatePos.$argCurrencyPos.
            $argPaymentTypePos. $argSupplierNamePos.$argCustomerNamePos.$argPaymentTermsPos.$argInvoiceDatePos.
            $lengthIdHex.$idHex.
            $lengthInvoiceNumberHex.$invoiceNumberHexPad.
            $lengthFiscalYearHex.$fiscalYearHexPad.
            $lengthTotalHex.$totalHexPad.
            $lengthStateHex.$stateHexPad.
            $lengthCurrencyHex.$currencyHexPad.
            $lengthPaymentTypeHex.$paymentTypeHexPad.
            $lengthSupplierNameHex.$supplierNameHexPad.
            $lengthCustomerNameHex.$customerNameHexPad.
            $lengthPaymentTermsHex.$paymentTermsHexPad.
            $lengthInvoiceDateHex.$invoiceDateHexPad;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"gas"=>"0x927c0","data"=> $call]],'id'=>67
        ];
        $params= json_encode($data);
        $this->unlockAccount();
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

        // hex de los parametros extra
        // recordar que hay que ultilizar otra posicion para el id al ser menos parametros
        $factoringTotalHex = $this->String2Hex($factoringTotal);
        $financialInstitutionNameHex = $this->String2Hex($financialInstitutionName);
        $factoringStateHex = $this->String2Hex($factoringState);
        $paymentDateHex = $this->String2Hex($paymentDate);
        $expirationDateHex = $this->String2Hex($expirationDate);
        $factoringExpirationDateHex = $this->String2Hex($factoringExpirationDate);

        // dezplazar los parametros
        $factoringTotalHexPad = str_pad($factoringTotalHex, 64, "0");
        $financialInstitutionNameHexPad = str_pad($financialInstitutionNameHex, 64, "0");
        $factoringStateHexPad = str_pad($factoringStateHex , 64, "0");
        $paymentDateHexPad = str_pad($paymentDateHex, 64, "0");
        $expirationDateHexPad = str_pad($expirationDateHex, 64, "0");
        $factoringExpirationDateHexPad = str_pad($factoringExpirationDateHex, 64, "0");

        //obtener el tamaño de los parametros
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $lengthFactoringTotalHex = str_pad(dechex(strlen($factoringTotalHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthFinancialInstitutionNameHex = str_pad(dechex(strlen($financialInstitutionNameHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthFactoringStateHex = str_pad(dechex(strlen($factoringStateHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthPaymentDateHex = str_pad(dechex(strlen($paymentDateHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthExpirationDateHex = str_pad(dechex(strlen($expirationDateHex )/2), 64, "0", STR_PAD_LEFT);
        $lengthFactoringExpirationDateHex = str_pad(dechex(strlen($factoringExpirationDateHex )/2), 64, "0", STR_PAD_LEFT);

        //posiciones de los parametros
        // bytes desde el id del metodo hasta el argumento, hex de (7*32)=224= e0 hex
        $argIdPosExtra = str_pad("e0", 64, "0", STR_PAD_LEFT);
        //(10*32)=320 = 140 hex
        $argFactoringTotalPos = str_pad("140", 64, "0", STR_PAD_LEFT);
        //(12*32)=384 = 180 hex
        $argFinancialInstitutionNamePos = str_pad("180", 64, "0", STR_PAD_LEFT);
        //(14*32)=448 = 1c0 hex
        $argFactoringStatePos = str_pad("1c0", 64, "0", STR_PAD_LEFT);
        //(16*32)=512 = 200 hex
        $argPaymentDatePos = str_pad("200", 64, "0", STR_PAD_LEFT);
        //(18*32)=576 = 240 hex
        $argExpirationDatePos = str_pad("240", 64, "0", STR_PAD_LEFT);
        //(20*32)=640 = 280 hex
        $argFactoringExpirationDatePos = str_pad("280", 64, "0", STR_PAD_LEFT);

        //keccak-256 de insertDocumentExtra(string,string,string,string,string,string,string) 65deb5ea0eb54f97b2d4b3e237b12df0720279a1fbb44fac45e7acfbfa4726f6
        $callExtra="0x65deb5ea".$argIdPosExtra.$argFactoringTotalPos.$argFinancialInstitutionNamePos.$argFactoringStatePos.
            $argPaymentDatePos.$argExpirationDatePos.$argFactoringExpirationDatePos.
            $lengthIdHex.$idHex.
            $lengthFactoringTotalHex.$factoringTotalHexPad.
            $lengthFinancialInstitutionNameHex.$financialInstitutionNameHexPad.
            $lengthFactoringStateHex.$factoringStateHexPad.
            $lengthPaymentDateHex.$paymentDateHexPad.
            $lengthExpirationDateHex.$expirationDateHexPad.
            $lengthFactoringExpirationDateHex.$factoringExpirationDateHexPad;

        $dataExtra  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"gas"=>"0x927c0","data"=> $callExtra]],'id'=>67
        ];
        $paramsExtra= json_encode($dataExtra);
        $this->unlockAccount();
        $handlerExtra = curl_init();
        curl_setopt($handlerExtra, CURLOPT_URL, self::URl);
        curl_setopt($handlerExtra, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($handlerExtra, CURLOPT_POST,true);
        curl_setopt($handlerExtra, CURLOPT_POSTFIELDS, $paramsExtra);
        curl_setopt($handlerExtra, CURLOPT_RETURNTRANSFER, true);
        $responseExtra = curl_exec ($handlerExtra);
        curl_close($handlerExtra);
        $jsonExtra=json_decode($responseExtra,true);
        $resultExtra=$jsonExtra['result'];

        return "hash de la transacion : ".$result." hash de la transacion extra : ".$resultExtra;

    }


//---------------------------------------------------setters------------------------------------------------------

    function setExpirationDate($id,$expirationDate){
        // hex de los parametros
        $idHex = $this->String2Hex($id);
        $expirationDateHex = $this->String2Hex($expirationDate);
        $expirationDateHexPad=str_pad($expirationDateHex, 64, "0");
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $leghtIdHex = str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        $leghtExpirationDateHex=str_pad(dechex(strlen($expirationDateHex )/2), 64, "0", STR_PAD_LEFT);
        // bytes desde el id del metodo hasta el argumento, hex de (2*32)=64 = 40
        $argIdPos = str_pad(40, 64, "0", STR_PAD_LEFT);
        //(5*32)=160 = a0
        $argExpirationDatePos =str_pad("a0", 64, "0", STR_PAD_LEFT);
        //keccak-256 de setExpirationDate(string,string) 6547f5faef5286132db7b1b4dc8bb572c7d5902a6913c8184de8207bdcb6e8f7
        $call="0x6547f5fa". $argIdPos .$argExpirationDatePos. $leghtIdHex.$idHex.$leghtExpirationDateHex.$expirationDateHexPad;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=> self::CONTRACT,"gas"=>"0x927c0","data"=> $call]],'id'=>67
        ];
        $params= json_encode($data);
        $this->unlockAccount();
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

        return "hash de la transferencia : ".$result;

    }

    function setFinancialInstitutionName($id,$financialInstitutionName){
        // hex de los parametros
        $idHex = $this->String2Hex($id);
        $financialInstitutionNameHex = $this->String2Hex($financialInstitutionName);
        $financialInstitutionNameHexPad=str_pad($financialInstitutionNameHex, 64, "0");
        //tomar el numero de caracteres, dividir por 2 para obtener el numero de bytes y pasar ese numero a hex y dezplazarlo
        $leghtIdHex = str_pad(dechex(strlen($idHex )/2), 64, "0", STR_PAD_LEFT);
        $leghtFinancialInstitutionNameHex=str_pad(dechex(strlen($financialInstitutionNameHex )/2), 64, "0", STR_PAD_LEFT);
        // bytes desde el id del metodo hasta el argumento, hex de (2*32)=64 = 40
        $argIdPos = str_pad(40, 64, "0", STR_PAD_LEFT);
        //(5*32)=160 = a0
        $argFinancialInstitutionNamePos =str_pad("a0", 64, "0", STR_PAD_LEFT);
        //keccak-256 de setFinancialInstitutionName(string,string) c9acf1e471801e7acd26ccd6b2b1aa6784f01f8a670de454135fd7c78fd263ff
        $call="0xc9acf1e4". $argIdPos .$argFinancialInstitutionNamePos. $leghtIdHex.$idHex.$leghtFinancialInstitutionNameHex.$financialInstitutionNameHexPad;

        $data  = [
            'jsonrpc'=>'2.0','method'=>'eth_sendTransaction','params'=>[[
                "from"=> self::ACCOUNT, "to"=>self::CONTRACT,"gas"=>"0x927c0","data"=> $call]],'id'=>67
        ];
        $params= json_encode($data);
        $this->unlockAccount();
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

        return "hash de la transferencia : ".$result;