const unityContainer = document.getElementById("unity-container");

chatInput.addEventListener("focus", () => {
    console.log("Chat focused");
    unityContainer.style.pointerEvents = "none"; // Bloquea clicks hacia Unity
});

chatInput.addEventListener("blur", () => {
    console.log("Chat blurred");
    unityContainer.style.pointerEvents = "auto"; // Reactiva Unity
});
