function Comparsa() {

    var num_documenti = document.getElementById("NumDocs").value;

    var input_da_generare = parseInt(num_documenti);
    
    var contenitore = document.getElementById("contenitore_input");

    contenitore.innerHTML = "";

    if (isNaN(input_da_generare)){
      contenitore.innerHTML = "";
      return;
    }

    document.getElementById("ris").innerHTML = "Inserisci il nome dei file";

    for (var i = 0; i < input_da_generare; i++) {
        var input = document.createElement("input");
        input.type = "file";
        input.name = "documento_" + i; 
        input.id = "input_" + i;
        input.onchange = function() { 
            /*if (this.files[0].size > 3000000) { 
                alert("File troppo grande!"); 
                this.value = ""; 
            }*/
            var input_pieni = true; 
            for (var j = 0; j < input_da_generare; j++) { 
                var f = document.getElementById("input_" + j); 
                if (!f || !f.files || f.files.length === 0) { 
                    input_pieni = false; 
                    break; 
                } 
            } 
            if (input_pieni) { 
                document.getElementById("ris").innerHTML = "Tutti i file sono stati selezionati."; 
                process();          
            }
        };
        contenitore.appendChild(input);
    }
}
