<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;

function slug_1 ( $name ) {

    return strtolower(preg_replace('/\./', '', preg_replace('/\s/', '-', trim($name))));

}
function string_1 ( $value ) {

    $value = $value === true ? 'true' : $value;
    $value = $value === false ? 'false' : $value;
    $values = ['', 'null', 'undefined'];
    if ( in_array(strval($value), $values) ) return null;
    return trim("{$value}") ?? null;

}
function bool_1 ( $value ) {

    $value = trim(strtolower($value));
    $values = ['true', '1', 't', 'yes', 'y', 'ya', 'yep', 'ok', 'on', 'done', 'always'];
    return in_array($value, $values);

}
function integer_1 ( $value ) {

    return (int)$value;

}
function float_1 ( $value, $decimal=2 ) {

    return round((float)$value, $decimal);

}
function positive_1 ( $value ) {

    return max(float($value), 0);

}
function parse_1 ( $value ) {

    if ( is_array($value) || is_object($value) ) return (array)$value;
    $array = json_decode($value) ?? [];
    if ( is_array($array) || is_object($array) ) return (array)$array;
    return (array)$array;

}
function plural_1 ( $value ) {

    return Str::plural( $value );

}
function random_int_key_1 ( $length=10, $sep='-' ) {

    $key = '';

    for ( $i = 1; $i < $length; $i++ ) {
        $key .= rand(0, 9);
        if ( $i % 3 === 0 && $i != 9 && $sep ) $key .= $sep;
    }

    return $key;

}
function utc_date_1 () {

    return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

}
function format_date_1 ( $date ) {

    return $date?->format('Y-m-d H:i:s');

}
function user_1 () {

    return Auth::guard('sanctum')?->user() ?? null;

}
function user_id_1 () {

    return user()?->id ?? 0;

}
function user_role_1 () {

    return match ( user()?->role ) { 1 => 'admin', 2 => 'vendor', 3 => 'client', 4 => 'subvendor' };

}
function failed_1 ( $response=[] ) {

    return response()->json(['status' => false, 'errors' => $response]);

}
function success_1 ( $response=[] ) {

    return response()->json(['status' => true] + $response);

}
function localize_1 ( $data, $all_langs=false ) {

    if ( $all_langs ) return $data;
    $lang = request()->input('local_lang', 'ar');
    $data = json_decode($data, true);
    return is_array($data) ? ($data[$lang] ?? null) : null;

}
function file_info_1 ( $file ) {

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
function upload_file_1 ( $file, $dir ) {

    if ( !$file ) return;
    $info = file_info($file);

    $path = $dir . '/' . date('Y') . '/' . date('m') . '/' . date('d');
    $info['url'] = $file->store($path);
    $info['dir'] = $dir;
    return $info;

}
function delete_file_1 ( $url ) {

    if ( !Storage::exists($url) ) return;
    Storage::delete($url);
    return true;

}
function upload_files_1 ( $files, $dir ) {

    foreach ( $files as $file ) upload_file($file, $dir);

}
function delete_files_1 ( $urls ) {

    foreach ( parse($urls) as $url ) delete_file($url);

}
function create_qrcode_1 ( $data, $margin=10, $size=500 ) {

    if ( !$data ) return;
    $qrCode = new QrCode(data: $data, margin: $margin, size: $size);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    $path = 'qrcodes/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . uniqid('qrcode_') . '.png';
    Storage::disk('public')->put($path, $result->getString());
    return $path;

}
