<?php

namespace Codingmstr\Helpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;

class Main {

    public function __construct () {

        // ...
        
    }
    public static function slug ( $name ) {

        return strtolower(preg_replace('/\./', '', preg_replace('/\s/', '-', trim($name))));
    
    }
    public static function string ( $value ) {
    
        $value = $value === true ? 'true' : $value;
        $value = $value === false ? 'false' : $value;
        $values = ['', 'null', 'undefined'];
        if ( in_array(strval($value), $values) ) return null;
        return trim("{$value}") ?? null;
    
    }
    public static function bool ( $value ) {
    
        $value = trim(strtolower($value));
        $values = ['true', '1', 't', 'yes', 'y', 'ya', 'yep', 'ok', 'on', 'done', 'always'];
        return in_array($value, $values);
    
    }
    public static function integer ( $value ) {
    
        return (int)$value;
    
    }
    public static function float ( $value, $decimal=2 ) {
    
        return round((float)$value, $decimal);
    
    }
    public static function positive ( $value ) {
    
        return max(float($value), 0);
    
    }
    public static function parse ( $value ) {
    
        if ( is_array($value) || is_object($value) ) return (array)$value;
        $array = json_decode($value) ?? [];
        if ( is_array($array) || is_object($array) ) return (array)$array;
        return (array)$array;
    
    }
    public static function plural ( $value ) {
    
        return Str::plural( $value );
    
    }
    public static function random_int_key ( $length=10, $sep='-' ) {
    
        $key = '';
    
        for ( $i = 1; $i < $length; $i++ ) {
            $key .= rand(0, 9);
            if ( $i % 3 === 0 && $i != 9 && $sep ) $key .= $sep;
        }
    
        return $key;
    
    }
    public static function utc_date () {
    
        return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    
    }
    public static function format_date ( $date ) {
    
        return $date?->format('Y-m-d H:i:s');
    
    }
    public static function user () {
    
        return auth('sanctum')?->user() ?? null;
    
    }
    public static function user_id () {
    
        return user()?->id ?? 0;
    
    }
    public static function user_role () {
    
        return match ( user()?->role ) { 1 => 'admin', 2 => 'vendor', 3 => 'client', 4 => 'subvendor' };
    
    }
    public static function failed ( $response=[] ) {
    
        return response()->json(['status' => false, 'errors' => $response]);
    
    }
    public static function success ( $response=[] ) {
    
        return response()->json(['status' => true] + $response);
    
    }
    public static function localize ( $data, $all_langs=false ) {
    
        if ( $all_langs ) return $data;
        $lang = request()->input('local_lang', 'ar');
        $data = json_decode($data, true);
        return is_array($data) ? ($data[$lang] ?? null) : null;
    
    }
    public static function file_info ( $file ) {
    
        $file_name = $file->getClientOriginalName();
        $file_type = $file->getMimeType();
        $ext = $file->extension();
        $size = $file->getSize();
    
        if ($size >= 1024 && $size < 1048576) $size = round($size / 1024, 1) . ' KB';
        else if ($size >= 1048576 && $size < 1073741824) $size = round($size / 1048576, 1) . ' MB';
        else if ($size >= 1073741824) $size = round($size / 1073741824, 1) . ' GB';
        else $size = $size ?? 0 . ' Byte';
    
        $name = array_values(array_filter(explode('.', $file_name), function($item){ return $item; }));
        if ( count($name) > 1 ) $name = implode('.', array_slice($name, 0, -1));
        else if ( count($name) == 1 ) $name = $name[0];
        else $name = $file_name;
    
        $type = explode('/', $file_type)[0] ?? 'file';
        if ( $type != 'image' && $type != 'video' && $type != 'audio' ) $type = 'file';
    
        return ['name' => $name, 'size' => $size, 'type' => $type, 'ext' => $ext];
    
    }
    public static function upload_file ( $file, $dir ) {
    
        if ( !$file ) return;
        $info = file_info($file);
    
        $path = $dir . '/' . date('Y') . '/' . date('m') . '/' . date('d');
        $info['url'] = $file->store($path);
        $info['dir'] = $dir;
        return $info;
    
    }
    public static function delete_file ( $url ) {
    
        if ( !Storage::exists($url) ) return;
        Storage::delete($url);
        return true;
    
    }
    public static function upload_files ( $files, $dir ) {
    
        foreach ( $files as $file ) upload_file($file, $dir);
    
    }
    public static function delete_files ( $urls ) {
    
        foreach ( parse($urls) as $url ) delete_file($url);
    
    }
    public static function create_qrcode ( $data, $margin=10, $size=500 ) {
    
        if ( !$data ) return;
        $qrCode = new QrCode(data: $data, margin: $margin, size: $size);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
    
        $path = 'qrcodes/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . uniqid('qrcode_') . '.png';
        Storage::disk('public')->put($path, $result->getString());
        return $path;
    
    }
    
}
