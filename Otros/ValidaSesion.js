 $(document).ready(function() {	
    function validaSesion() {
 
$.ajax({
    type: "POST",
    url: 'Otros/sesion.php',
    data: $(this).serialize(),
    success: function(response)
    {
        var jsonData = JSON.parse(response);

        if (jsonData.success == "1")
        {
            console.log("Sesión activa")
          //  alert("Exitoso");

        }else{
            console.log("Salir");
            alert("Finalizó la sesión");
         
        }
   }
}
)
    }
    setInterval(validaSesion, 300000);
});
  
 