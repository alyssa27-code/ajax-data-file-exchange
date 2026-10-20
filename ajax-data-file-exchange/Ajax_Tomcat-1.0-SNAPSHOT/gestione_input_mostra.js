function Check() {
    var valore_input_1 = document.getElementById("MyUsername").value.trim();
    var valore_input_2 = document.getElementById("MyExt").value.trim();

    if (valore_input_1 !== "" && valore_input_2 !== "") {
        process();
    }
}
