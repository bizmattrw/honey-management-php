    </div> <!-- content -->
</div> <!-- flex -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS functions -->
<script src="/assets/js/script.js"></script>

</body>
</html>
<script>
function toggleSidebar() {
    let sidebar = document.getElementById("sidebar");
    let texts = document.querySelectorAll(".text");

    if (sidebar.style.width === "80px") {
        sidebar.style.width = "250px";
        texts.forEach(t => t.style.display = "inline");
    } else {
        sidebar.style.width = "80px";
        texts.forEach(t => t.style.display = "none");
    }
}

function toggleStock() {
    let menu = document.getElementById("stockMenu");
    menu.classList.toggle("d-none");
}
</script>