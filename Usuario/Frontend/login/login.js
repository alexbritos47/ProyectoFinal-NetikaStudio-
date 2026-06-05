  setTimeout(() => {

  const loader = document.getElementById("loader");

  loader.style.opacity = "0";

  setTimeout(() => {
    loader.style.display = "none";
    document.getElementById("main").style.display = "flex";
  }, 1000);

}, 3000); // 3 segundos