function genrate(){
    var minute,sms,data;
    minute=Math.floor(Math.random()*10);
    sms=Math.floor(Math.random()*10);
    data=Math.floor(Math.random()*10);

    document.getElementById("minute").value=minute
    document.getElementById("sms").value=sms
    document.getElementById("data").value=data
}

document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('input', function() {
        if (parseInt(this.value) < 0) {
            this.value = 0; // Reset to 0 if negative value is entered
        }
    });
});