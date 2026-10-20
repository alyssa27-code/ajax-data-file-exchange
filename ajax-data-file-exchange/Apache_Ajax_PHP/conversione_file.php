<?php

include "gestione_contenuti_file.php";

function converti_file($inputPath, $outputPath) {

    // realpath() converte un percorso relativo in uno assoluto.
    $inputAssoluto  = realpath($inputPath);
    $outputAssoluto = realpath(dirname($outputPath)) . "/" . basename($outputPath);

    // PATHINFO_EXTENSION è la costante che permette di isolare i caratteri che seguono l'ultimo punto del nome del file.
    $extIn  = strtolower(pathinfo($inputAssoluto, PATHINFO_EXTENSION));
    $extOut = strtolower(pathinfo($outputAssoluto, PATHINFO_EXTENSION));

    // Conversione in PDF.
    if ($extOut === "pdf") {

        switch ($extIn) {

            case "txt":
                 // Il file TXT viene convertito in un file DOCX temporaneo.
                $docxTemp = $inputAssoluto . ".docx";
                $conv1 = converti_con_libreoffice($inputAssoluto, $docxTemp);

                if (!$conv1 || !file_exists($docxTemp)) return false;

                // Il file DOCX viene convertito in un file PDF.
                $conv2 = converti_con_libreoffice($docxTemp, $outputAssoluto);

                // Viene eliminato il file DOCX temporaneo.
                if (file_exists($docxTemp)) unlink($docxTemp);

                return $conv2;

            case "doc":
            case "docx":
                return converti_con_libreoffice($inputAssoluto, $outputAssoluto);

            case "png":
            case "jpg":
            case "jpeg":
                return converti_con_libreoffice($inputAssoluto, $outputAssoluto);

            case "pdf":
                return converti_con_libreoffice($inputAssoluto, $outputAssoluto);

            default:
                return false;
        }
    }

    // Conversione in DOC/DOCX.
    if ($extOut === "doc" || $extOut === "docx") {

        switch ($extIn) {

            case "txt":
            case "doc":
            case "docx":
                return converti_con_libreoffice($inputAssoluto, $outputAssoluto);

            case "pdf":

                $conv = converti_con_libreoffice($inputAssoluto, $outputAssoluto);
                if ($conv && file_exists($outputAssoluto)) return true;

                // Trasferimento dei contenuti del file originario nel file convertito.
                return converti_file_contenuto($inputAssoluto, $outputAssoluto);

            default:
                return false;
        }
    }

    // Conversione in TXT, OCR incluso.
    if ($extOut === "txt") {

        switch ($extIn) {

            case "txt":
                copy($inputAssoluto, $outputAssoluto);
                return file_exists($outputAssoluto);

            case "doc":
            case "docx":
                return converti_con_libreoffice($inputAssoluto, $outputAssoluto);

            case "pdf":
                if (pdf_to_txt_pdftotext($inputAssoluto, $outputAssoluto)) {
                return true;
                }
                return converti_con_ocr($inputAssoluto, $outputAssoluto);

            default:
                return false;
        }
    }

    // Conversione in PNG, JPG o JPEG.
    if (in_array($extOut, ["png", "jpg", "jpeg"])) {

        switch ($extIn) {

            case "png":
            case "jpg":
            case "jpeg":
                copy($inputAssoluto, $outputAssoluto);
                return file_exists($outputAssoluto);

            case "pdf":
                return pdf_to_image_ghostscript($inputAssoluto, $outputAssoluto, $extOut);

            default:
                return false;
        }
    }

    // Conversione in ZIP.
    if ($extOut === "zip") {
        return converti_in_zip($inputAssoluto, $outputAssoluto);
    }

    return false;
}

function converti_con_libreoffice($inputAssoluto, $outputAssoluto) {

    $extOut = strtolower(pathinfo($outputAssoluto, PATHINFO_EXTENSION));
    $outputDir = dirname($outputAssoluto);

    $soffice = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';

    switch ($extOut) {

    // Il parametro --headless permette a LibreOffice di essere eseguito senza interfaccia grafica (GUI), quindi i background.
        case "docx":
            $cmd = $soffice . ' --headless --convert-to docx:"MS Word 2007 XML" --outdir "'.$outputDir.'" "'.$inputAssoluto.'"';
            break;

        case "doc":
            $cmd = $soffice . ' --headless --convert-to doc:"MS Word 97" --outdir "'.$outputDir.'" "'.$inputAssoluto.'"';
            break;

        case "txt":
            $cmd = $soffice . ' --headless --convert-to txt:"Text" --outdir "'.$outputDir.'" "'.$inputAssoluto.'"';
            break;

        case "pdf":

    // PATHINFO_FILENAME è una costante che specifica il nome base del file di input (es: Prova_1).
    $inputBase = pathinfo($inputAssoluto, PATHINFO_FILENAME);

    // Il file PDF viene generato da LibreOffice (es: Prova_1.pdf)
    $generated = $outputDir . "/" . $inputBase . ".pdf";
    $cmd = $soffice . ' --headless --convert-to pdf --outdir "'.$outputDir.'" "'.$inputAssoluto.'"';
    exec($cmd);

    if (!file_exists($generated)) {
        return false;
    }

    // Viene rinominato il file generato con il nome richiesto dall’utente.
    if ($generated !== $outputAssoluto) {

        if (file_exists($outputAssoluto)) {
            unlink($outputAssoluto);
        }

        rename($generated, $outputAssoluto);
    }

    return file_exists($outputAssoluto);

        default:
            return false;
    }

    exec($cmd);

    $inputBase = pathinfo($inputAssoluto, PATHINFO_FILENAME);
    $generated = $outputDir . DIRECTORY_SEPARATOR . $inputBase . '.' . $extOut;

    if (file_exists($generated) && $generated !== $outputAssoluto) {
        unlink($outputAssoluto);
        rename($generated, $outputAssoluto);
    }

    if (file_exists($outputAssoluto)) return true;

    // Trasferimento dei contenuti del file originario nel file convertito.
    return converti_file_contenuto($inputAssoluto, $outputAssoluto);
}

function converti_con_ocr($inputAssoluto, $outputAssoluto) {

    // Percorso dell’eseguibile Tesseract.
    $tesseract = '"C:\\Program Files\\Tesseract-OCR\\tesseract.exe"';

    // Percorso della cartella dei dizionari linguistici di Tesseract.
    $tessdata  = '"C:\\Program Files\\Tesseract-OCR\\tessdata"';

    // preg_replace() esegue una ricerca e sostituzione tramite espressioni regolari.
    // '' è il testo sostitutivo, ovvero una stringa vuota, che cancella la parte trovata.
    // $outputAssoluto è la variabile di input contenente la stringa originale (es. /cartella/file.docx).
    // /\.[^.\s]{3,4}$/ è l'espressione regolare. 
    // / ... / delimitana l'espressione regolare.
    // \. cerca un punto letterale.
    // [^.\s]{3,4} cerca una sequenza di caratteri che non contiene un punto (.) né spazi (\s), che sia lunga esattamente 3 o 4 caratteri, quindi adatta per estensioni come .png, .html, .jpeg.
    // $ indica la fine della stringa.
    $outputNoExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $outputAssoluto);

    //Tesseract aggiunge l'estensione .txt in autamico.
    $cmd = $tesseract . ' "' . $inputAssoluto . '" "' . $outputNoExt . '" --tessdata-dir ' . $tessdata . ' -l ita';

    exec($cmd);

    if (file_exists($outputAssoluto)) return true;

    // Trasferimento dei contenuti del file originario nel file convertito.
    return converti_file_contenuto($inputAssoluto, $outputAssoluto);
}

function converti_in_zip($inputAssoluto, $outputAssoluto) {

    // ZipArchive() è il costruttore della classe PHP ZipArchive e server per creare una cartella zip 
    // vuota (oggetto PHP).
    $zip = new ZipArchive();

    // Si prova ad aprire il file ZIP con il metodo open().
    // Se non esiste la cartella ZIP, viene creata con ZipArchive::CREATE.
    // Se l’operazione ha successo, open() restituisce TRUE e si entra nel blocco if.
    if ($zip->open($outputAssoluto, ZipArchive::CREATE) === TRUE) {
        
    // Viene aggiunto il file di input all’archivio ZIP con il metodo addFile().
    // $inputAssoluto è il percorso assoluto del file da inserire.
    // basename($inputAssoluto) trova il nome del file da inserire all’interno dello ZIP (solo il nome, senza percorso).
        $zip->addFile($inputAssoluto, basename($inputAssoluto));

    // Il metodo close() chiude la cartella ZIP.
        $zip->close();
        return true;
    }
    else
    {
        return false;
    }
}

function pdf_to_image_ghostscript($inputAssoluto, $outputAssoluto, $format) {

    // Percorso dell’eseguibile Ghostscript in modalità console (gswin64c.exe).
    $gs = '"C:\\Program Files\\gs\\gs10.07.1\\bin\\gswin64c.exe"';

    // $devices è un array associativo che mappa il formato richiesto (png, jpg, jpeg) al device Ghostscript corrispondente.
    // ESEMPI: png16m per un file PNG a 24 bit, oppure jpeg per un file JPEG.
    $devices = [
        "png"  => "png16m",
        "jpg"  => "jpeg",
        "jpeg" => "jpeg"
    ];

    // Viene verificato che il formato richiesto sia supportato.
    if (!isset($devices[$format])) return false;

    $device = $devices[$format];

    // Ghostscript prendere solamente la prima pagina del file PDF e 
    // salva l'output direttamente nel percorso finale.
    // -dNOPAUSE: il programma scorre le pagine del PDF in automatico.
    // -dBATCH: il programma si chiude in automatico non appena ha finito il lavoro.
    // -sDEVICE=$device: viene scelto il formato finale del file.
    // -r300: viene impostata la risoluzione a 300 DPI. 
    // -dFirstPage=1: indica da quale pagina del PDF originale iniziare a lavorare; in questo caso, la prima pagina.
    // -dLastPage=1: indica a quale pagina del PDF originale fermarsi; in questo caso, sempre la prima pagina. 
    // -sOutputFile=...: viene specificato il nome e il percorso in cui salvare il nuovo file creato.
    // $inputAssoluto è il file PDF originale di partenza che si vuole trasformare.
    $cmd = $gs
        . " -dNOPAUSE -dBATCH"
        . " -sDEVICE=$device"
        . " -r300"
        . " -dFirstPage=1"
        . " -dLastPage=1"
        . " -sOutputFile=\"$outputAssoluto\""
        . " \"$inputAssoluto\"";

    exec($cmd);

    return file_exists($outputAssoluto);
}

function pdf_to_txt_pdftotext($input, $output) {

    // Percorso dell’eseguibile pdftotext, parte del pacchetto Poppler.
    $pdftotext = '"C:\\Program Files\\poppler\\Library\\bin\\pdftotext.exe"';
    $cmd = $pdftotext . " \"$input\" \"$output\"";
    exec($cmd);
    return file_exists($output);
}

function immagine_to_pdf_chrome($inputAssoluto, $outputAssoluto) {

    // Percorso dell’eseguibile di Google Chrome.
    $chrome = '"C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe"';

    // Viene costruito un percorso per un file HTML temporaneo nella stessa cartella dell'immagine.
    $htmlTemp = dirname($inputAssoluto) . "/" . basename($inputAssoluto) . ".html";

    // str_replace("\\", "/", $inputAssoluto) converte i backslash di Windows in slash, rendendoli così compatibili con URL.
    $html = '<html><body style="margin:0;padding:0;">
                <img src="file:///' . str_replace("\\", "/", $inputAssoluto) . '" style="width:100%;height:auto;">
             </body></html>';

    file_put_contents($htmlTemp, $html);

    // --disable-gpu disabilita l'accelerazione hardware della scheda video (GPU), costringendo Chrome a ricorrere alla CPU.
    // --no-sandbox disattiva la "sandbox", ovvero il sistema di isolamento di sicurezza che Chrome usa per 
    // impedire alle pagine web di accedere ai file di sistema del computer.
    // "file:///" è il protocollo di rete standard per indicare a un browser che non deve cercare un sito web su internet (http://), 
    // ma deve aprire un file locale presente dentro il disco rigido del computer/server.
    // str_replace("\\", "/", $htmlTemp) prende il percorso del file HTML temporaneo ($htmlTemp) e sostituisce tutte le barre retroverse 
    // (backslashes "\", che sono tipiche di Windows) con le barre normali (slashes "/").
    $cmd = $chrome
        . ' --headless --disable-gpu --no-sandbox'
        . ' --print-to-pdf="' . $outputAssoluto . '"'
        . ' "file:///' . str_replace("\\", "/", $htmlTemp) . '"';

    exec($cmd);

    unlink($htmlTemp);

    if (file_exists($outputAssoluto)) return true;

    // Trasferimento dei contenuti del file originario nel file convertito.
    return converti_file_contenuto($inputAssoluto, $outputAssoluto);
}

function converti_pdf_in_docx_ocr($inputAssoluto, $outputAssoluto) {

    // Viene definito un percorso per una cartella temporanea di lavoro.
    // dirname($outputAssoluto) indica il nome della stessa cartella del file di output. 
    // uniqid() serve per generare un identificativo univoco per la cartella temporanea. 
    $tempDir = dirname($outputAssoluto) . "/temp_ocr_" . uniqid();
    mkdir($tempDir);

    // Conversione PDF to PNG tramite Ghostscript.
    pdf_to_image_ghostscript($inputAssoluto, $tempDir . "/page.png", "png");

    // La funzione glob() cerca nella cartella temporanea tutti i file che corrispondono al pattern specificato,
    // in questo caso i file PNG generati da Ghostscript. 
    // Viene restituito un array contenente i percorsi di tutte le corrispondenze trovate.
    $pngFiles = glob($tempDir . "/gs_temp_*_page.png");

    if (empty($pngFiles)) {
        array_map('unlink', glob("$tempDir/*"));
        rmdir($tempDir);
        return false;
    }

    // natsort() ordina in maniera "naturale" per preservare la sequenza numerica corretta delle pagine 
    // (es. page_2 prima di page_10).
    natsort($pngFiles);

    $textFinale = "";
    $i = 0;

    // Conversione PNG to TXT, utilizzando OCR.
    foreach ($pngFiles as $pngPage) {

        $txtFile = "$tempDir/page_$i.txt";
        
        // Viene chiamata la funzione OCR passando il file PNG di input e il percorso del file TXT di output.
        converti_con_ocr($pngPage, $txtFile);

        if (file_exists($txtFile)) {

            // Una volta letto il contenuto del file di testo appena generato, quest'ultimo viene agganciato alla variabile $textFinale. 
            // I due a capo (\n\n)  servono per separare le pagine tra loro.
            $textFinale .= file_get_contents($txtFile) . "\n\n";
        }

        $i++;
    }

    // Viene definito un percorso per un nuovo file di testo unico che verrà poi convertito in un documento.
    $txtTemp = "$tempDir/ocr_finale.txt";
    file_put_contents($txtTemp, $textFinale);

    // Conversione TXT to DOC/DOCX tramite LibreOffice.
    $conv = converti_con_libreoffice(realpath($txtTemp), $outputAssoluto);

    // Viene fatta una pulizia finale dei file temporaneii e della cartella temporanea.
    array_map('unlink', glob("$tempDir/*"));
    rmdir($tempDir);

    if ($conv && file_exists($outputAssoluto)) {
        return true;
    }

    // Trasferimento dei contenuti del file originario nel file convertito.
    return converti_file_contenuto($inputAssoluto, $outputAssoluto);
}

?>
