<?php

function estrai_contenuto($inputAssoluto) {

    $ext = strtolower(pathinfo($inputAssoluto, PATHINFO_EXTENSION));

    // $contenuto è un array che fungerà da "scatola" in cui inserirei dati estratti. 
    // L'array è costituito da tre chiavi: il tipo di file, il testo trovato (una stringa vuota all'inizio) 
    // e una lista di immagini (array vuoto).
    $contenuto = [
        "tipo"     => null,
        "testo"    => "",
        "immagini" => []
    ];

    switch ($ext) {

        case "txt":
            $contenuto["tipo"]  = "testo";
            $contenuto["testo"] = file_get_contents($inputAssoluto);
            break;

        case "png":
        case "jpg":
        case "jpeg":
            $contenuto["tipo"] = "immagine";
            $contenuto["immagini"][] = $inputAssoluto;
            break;

        case "pdf":
            // Estrazione del testo.
            $txtTemp = $inputAssoluto . "_temp.txt";
            converti_con_libreoffice($inputAssoluto, $txtTemp);

            if (file_exists($txtTemp)) {
                $contenuto["testo"] = file_get_contents($txtTemp);
                unlink($txtTemp);
            }

            // Estrazione di immagini.
            $tempDir = dirname($inputAssoluto) . "/estrazione_" . uniqid();
            mkdir($tempDir);

            $pngs = pdf_to_png_ghostscript($inputAssoluto, $tempDir);
            if (!empty($pngs)) {
                $contenuto["immagini"] = $pngs;
            }

            // Se la chiave 'testo' non è vuota, il tipo viene definito come 'misto', oovero contiene sia testo che immagini. 
            // Se invece è vuota, il tipo  viene definito come 'immagine'.
            $contenuto["tipo"] = (!empty($contenuto["testo"]) ? "misto" : "immagine");
            break;

        case "doc":
        case "docx":
            $txtTemp = $inputAssoluto . "_temp.txt";
            converti_con_libreoffice($inputAssoluto, $txtTemp);

            if (file_exists($txtTemp)) {
                $contenuto["testo"] = file_get_contents($txtTemp);
                unlink($txtTemp);
            }

            $contenuto["tipo"] = "testo";
            break;
    }

    return $contenuto;
}

function ricostruisci_file($contenuto, $outputAssoluto) {

    $ext = strtolower(pathinfo($outputAssoluto, PATHINFO_EXTENSION));

    switch ($ext) {

        case "txt":
            file_put_contents($outputAssoluto, $contenuto["testo"]);
            return true;

        case "pdf":
            if (!empty($contenuto["testo"])) {
                $tempTxt = $outputAssoluto . "_temp.txt";
                file_put_contents($tempTxt, $contenuto["testo"]);
                converti_con_chrome($tempTxt, $outputAssoluto);
                unlink($tempTxt);
                return file_exists($outputAssoluto);
            }

            if (!empty($contenuto["immagini"])) {
                return immagine_to_pdf_chrome($contenuto["immagini"][0], $outputAssoluto);
            }
            break;

        case "png":
        case "jpg":
        case "jpeg":
            if (!empty($contenuto["immagini"])) {
                copy($contenuto["immagini"][0], $outputAssoluto);
                return true;
            }

            if (!empty($contenuto["testo"])) {
                $tempPdf = $outputAssoluto . "_temp.pdf";
                $tempTxt = $outputAssoluto . "_temp.txt";

                file_put_contents($tempTxt, $contenuto["testo"]);
                converti_con_chrome($tempTxt, $tempPdf);
                pdf_to_image_ghostscript($tempPdf, $outputAssoluto, $ext);

                unlink($tempTxt);
                unlink($tempPdf);

                return file_exists($outputAssoluto);
            }
            break;

        case "doc":
        case "docx":
            $tempTxt = $outputAssoluto . "_temp.txt";
            file_put_contents($tempTxt, $contenuto["testo"]);
            converti_con_libreoffice(realpath($tempTxt), $outputAssoluto);
            unlink($tempTxt);
            return file_exists($outputAssoluto);
    }

    return false;
}

function converti_file_contenuto($inputAssoluto, $outputAssoluto) {
    $contenuto = estrai_contenuto($inputAssoluto);
    return ricostruisci_file($contenuto, $outputAssoluto);
}

?>