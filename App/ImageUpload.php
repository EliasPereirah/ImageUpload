<?php
namespace App;
use Cocur\Slugify\Slugify;
class ImageUpload{
    public array $errors = [];
    private array $globalFile;
    private bool $hasUploaded = false;
    private int $maxFileNameLength = 100;
    public function doUpload($globalFile, $uploadTo, $maxKilobytes = 2000):void{
        $this->globalFile = $globalFile;
        $pn = pathinfo($globalFile['name'], PATHINFO_FILENAME);
        if(strlen($pn) == 0){
            // ex: .png or just png instead of: name.png
            $this->errors[] =  "Arquivo não tem extensão ou não tem nome";
        }else{
            if(!$this->hasError()){
                if(!$this->isFileTooHeavy($maxKilobytes)){
                    if($this->isExtensionValid() && $this->isMimeValid()){
                        if($this->isImage()){
                            if(is_dir($uploadTo)){
                                $fileName = $this->getGoodFileName($uploadTo);
                                $destination = "{$uploadTo}/{$fileName}";
                                $this->realDoUpload($globalFile['tmp_name'], $destination);
                            }else{
                                $this->errors[] = "<b>Para o admin:</b> O diretório informado é inválido!";
                            }
                        }
                    }
                }

            }
        }


    }

    private function hasError():bool{
        if($this->globalFile['error']){
            $this->errors[] = "Ops... houve um erro, tente novamente!";
            return true;
        }
        return false;
    }


    private function getGoodFileName(string $uploadTo):string{

        $fileLength = strlen($this->globalFile['name']);
        if($fileLength > $this->maxFileNameLength){
            $extension = pathinfo($this->globalFile['name'], PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            $name = rand(1,9999).'-'.rand(1,9999).".{$extension}";
            $path = "{$uploadTo}/{$name}";
            while (file_exists($path)){
                $name = rand(1, getrandmax()).'-'.rand(1, getrandmax()).".{$extension}";
                $path = "{$uploadTo}/{$name}";
            }
            return $name;

        }else{
            $Slugify = new Slugify();
            $name = pathinfo($this->globalFile['name'], PATHINFO_FILENAME);
            $extension = pathinfo($this->globalFile['name'], PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            $name = $Slugify->slugify($name);
            $name .= ".{$extension}";
            $path = "{$uploadTo}/{$name}";
            $originalName =  $name;
            while (file_exists($path)){
                $name = rand(1, getrandmax())."-{$originalName}";
                $path = "{$uploadTo}/{$name}";
            }
            return $name;

        }
    }

    private function isFileTooHeavy(int $maxKB):bool{
        $size =  $this->globalFile['size'] / 1024;
        if($size > $maxKB){
            $this->errors[] = "O arquivo não pode ter mais que {$maxKB}KB!";
            return true;
        }
        return false;
    }

    private function isMimeValid():bool{
        $validMimes = ['image/webp','image/webp','image/gif','image/jpeg','image/png','image/gif'];
        $mime = mime_content_type($this->globalFile['tmp_name']);
        if(in_array($mime, $validMimes)){
            return true;
        }
        $this->errors[] = "Arquivos ".htmlentities($mime)." não é válido!";
        return false;
    }

    private function isExtensionValid():bool{
        $extension = pathinfo($this->globalFile['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        $validExtension = ['webp', 'webp', 'gif', 'jpeg', 'jpg', 'png','gif'];
        if(in_array($extension, $validExtension)){
            return true;
        }
        $this->errors[] = "O formato ".htmlentities($extension)." não é valido!";
        return false;
    }

    private function realDoUpload(string $filename, string $destination):bool{
        if(move_uploaded_file($filename, $destination)){
            $this->hasUploaded = true;
            return true;
        }
        $this->errors[] = "Não foi possível fazer o upload!";
        $this->hasUploaded = false;
        return false;
    }

    /* @param  $length int Max file name length*/
    public function setMaxFileNameLength(int $length){
        $this->maxFileNameLength = $length;
        if($length < 15){
            $this->maxFileNameLength = 15;
        }
        if($length > 200){
            $this->maxFileNameLength = 200;
        }
    }

    private function isImage():bool{
        $filename = $this->globalFile['tmp_name'];
        $type = @exif_imagetype($filename);

        switch($type){
            case 1:
                // gif
                $isImage = @imagecreatefromgif($filename);
                break;
            case 2:
                // jpg
                $isImage = @imagecreatefromjpeg($filename);
                break;
            case 3:
                // png
                $isImage = @imagecreatefrompng($filename);
                break;
            case 18:
                // webp
                $isImage = @imagecreatefromwebp($filename);
                break;
            default:
                $isImage = false;
                $this->errors[] = "Arquivo não é uma imagem válida." ;
        }
        if($isImage){
            // don't send return $isImage directly, comparison with === would fail
            return true;
        }
        $this->errors[] = "Não conseguimos identificar o formato do arquivo.";
        return false;

    }

    public function wasUploadedSuccess(){
        if(!$this->hasUploaded){
            if($this->errors == []){
                exit("A function <b>wasUploadedSuccess()</b> só deve ser chamada após <b>doUpload()</b>");
            }
            return false;
        }
        return true;

    }

}
