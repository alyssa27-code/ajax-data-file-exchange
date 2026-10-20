function Comparsa() {

    var utente = document.getElementById("MyUsername").value.trim();
    var num_documenti = document.getElementById("NumDocs").value.trim();

    if (!isNaN(num_documenti) && utente != "" && num_documenti != "") {

        var input_da_generare = parseInt(num_documenti);

        var contenitore = document.getElementById("contenitore_input");

        contenitore.innerHTML = "";

        document.getElementById("ris").innerHTML = "Inserisci il nome e l'estensione dei file";

        var pieni = false;

        for (var i = 0; i < input_da_generare; i++) {
            var input = document.createElement("input");
            input.type = "text";
            input.name = "documento_" + i;
            input.id = "input_" + i;
            contenitore.appendChild(input);

        }

        //var chiamata = false;

        for (var i = 0; i < input_da_generare; i++) {
            var input = document.getElementById("input_" + i);

            input.addEventListener("input", function () {
                if (this.value.trim() !== "") {
                    process();
                }

            });
        }

    }
    else {
        contenitore.innerHTML = "";
        return;
    }
}
