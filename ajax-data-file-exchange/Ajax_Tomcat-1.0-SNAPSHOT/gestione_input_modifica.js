var input_da_generare = 0;

function Comparsa() {

    var num_documenti = document.getElementById("NumDocs").value;

    input_da_generare = parseInt(num_documenti);
    
    var contenitore_1 = document.getElementById("contenitore_input_1");

    var contenitore_2 = document.getElementById("contenitore_input_2");

    var contenitore_3 = document.getElementById("contenitore_input_3");

    var contenitore_4 = document.getElementById("contenitore_input_4");

    contenitore_1.innerHTML = "";
    contenitore_2.innerHTML = "";
    contenitore_3.innerHTML = "";
    contenitore_4.innerHTML = "";

    if (isNaN(input_da_generare)){
      contenitore_1.innerHTML = "";
      contenitore_2.innerHTML = "";
      contenitore_3.innerHTML = "";
      contenitore_4.innerHTML = "";
      return;
    }

    document.getElementById("ris_1").innerHTML = "Inserisci il nome dei file<br>";

    for (var i = 0; i < input_da_generare; i++) {
        var input = document.createElement("input");
        input.type = "text";
        input.name = "documento_" + i; 
        input.id = "input_doc_" + i;
        contenitore_1.appendChild(input);
    }

    document.getElementById("ris_2").innerHTML = "Inserisci l'estensione dei file<br>";

    for (var i = 0; i < input_da_generare; i++) {
        var input = document.createElement("input");
        input.type = "text";
        input.name = "estensione_" + i; 
        input.id = "input_ext_" + i;
        contenitore_2.appendChild(input);
    }

    document.getElementById("ris_3").innerHTML = "Inserisci il nuovo nome dei file<br>";

    for (var i = 0; i < input_da_generare; i++) {
        var input = document.createElement("input");
        input.type = "text";
        input.name = "nuovo_documento_" + i; 
        input.id = "nuovo_input_doc_" + i;
        contenitore_3.appendChild(input);
    }

    document.getElementById("ris_4").innerHTML = "Inserisci la nuova estensione dei file<br>";

    for (var i = 0; i < input_da_generare; i++) {
        var input = document.createElement("input");
        input.type = "text";
        input.name = "nuova_estensione_" + i; 
        input.id = "nuovo_input_ext_" + i;
        contenitore_4.appendChild(input);
    }

    for (var i = 0; i < input_da_generare; i++) {
    document.getElementById("input_doc_" + i).addEventListener("change", Check);
    document.getElementById("input_ext_" + i).addEventListener("change", Check);
    document.getElementById("nuovo_input_doc_" + i).addEventListener("change", Check);
    document.getElementById("nuovo_input_ext_" + i).addEventListener("change", Check);
}

}

function Check() {

    for (var i = 0; i < input_da_generare; i++) {

        var docPrima = document.getElementById("input_doc_" + i).value.trim();
        var extPrima = document.getElementById("input_ext_" + i).value.trim();
        var docDopo  = document.getElementById("nuovo_input_doc_" + i).value.trim();
        var extDopo  = document.getElementById("nuovo_input_ext_" + i).value.trim();

        // L’estensione nuova è valida SOLO se:
        // - inizia con "."
        // - ha almeno 2 caratteri (es: ".p" NO, ".pdf" SÌ)
        var ext_completata = extDopo.startsWith(".") && extDopo.length >= 3;

        if (
            docPrima === "" ||
            extPrima === "" ||
            docDopo  === "" ||
            !ext_completata
        ) {
            return; // almeno un campo non è completo → NON chiamare process()
        }
    }

    // Se arrivi qui, TUTTI i campi sono completi
    process();
}


