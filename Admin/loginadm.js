setTimeout(() => {
  const loader = document.getElementById("loader");
  loader.style.opacity = "0";

  setTimeout(() => {
    loader.style.display = "none";
    document.getElementById("main").style.display = "flex";
  }, 1000);

}, 3000);

const boton = document.getElementById("btnIngresar");

boton.addEventListener("click", function() {
    const usuario = document.getElementById("user").value;
    const password = document.getElementById("password").value;
    
    console.log("Usuario:", usuario);
    console.log("Password:", password);
});

if (usuario === "admin" && password === " ")