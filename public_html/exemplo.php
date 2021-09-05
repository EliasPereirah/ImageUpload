<?php
use App\ImageUpload;
require("../vendor/autoload.php");

$destino = __DIR__ . '/fotos';
if (!empty($_FILES['foto'])) {
    $Upload = new ImageUpload();
    // $Upload->setMaxFileNameLength(80); // optional - default 100
    $Upload->doUpload($_FILES['foto'], $destino);
    if($Upload->wasUploadedSuccess()){
        echo "Upload realizado com sucesso";
    }else{
        echo "Erros: ";
        foreach ($Upload->errors as $erro) {
            echo "{$erro}<br>";
        }
    }
}


