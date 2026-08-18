<?php
declare(strict_types=1);
namespace App\Media;
use App\Entity\Institution;
use App\Entity\MediaAsset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class MediaUploader
{
    private const ALLOWED=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
    public function __construct(private string $projectDir){}
    public function upload(UploadedFile $file,string $altText,Institution $institution):MediaAsset
    {
        if($file->getSize()===false||$file->getSize()>5_000_000)throw new \InvalidArgumentException('A fájl legfeljebb 5 MB lehet.');
        $size=(int)$file->getSize();$originalName=$file->getClientOriginalName();$mime=$file->getMimeType()??'';$extension=self::ALLOWED[$mime]??throw new \InvalidArgumentException('Csak JPEG, PNG, WebP vagy PDF tölthető fel.');
        $tenantDir=$institution->getSlug();$name=bin2hex(random_bytes(16)).'.'.$extension;$relative=$tenantDir.'/'.$name;$target=$this->projectDir.'/var/uploads/'.$tenantDir;
        $file->move($target,$name);
        return new MediaAsset($institution,$originalName,$relative,$mime,$size,trim($altText));
    }
    public function path(MediaAsset $asset):string
    {
        $root=$this->projectDir.'/var/uploads';$path=$root.'/'.$asset->getStoragePath();$realRoot=realpath($root);$realPath=realpath($path);
        if($realRoot===false||$realPath===false||!str_starts_with($realPath,$realRoot.DIRECTORY_SEPARATOR))throw new \RuntimeException('A médiafájl nem található.');
        return $realPath;
    }
    public function delete(MediaAsset $asset):void
    {
        try{$path=$this->path($asset);}catch(\RuntimeException){return;}if(!unlink($path))throw new \RuntimeException('A médiafájl nem törölhető.');
    }
}
