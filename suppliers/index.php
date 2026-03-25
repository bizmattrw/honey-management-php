<?php
include("../config/db.php");

// ================= CREATE =================
if (isset($_POST['action']) && $_POST['action'] == "create") {
    $stmt = $conn->prepare("
        INSERT INTO suppliers 
        (Name, Phone, provinceCode, districtCode, sectorCode, cellCode, villageCode)
        VALUES (?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $_POST['Name'],
        $_POST['Phone'],
        $_POST['provinceCode'],
        $_POST['districtCode'],
        $_POST['sectorCode'],
        $_POST['cellCode'],
        $_POST['villageCode']
    ]);
    header("Location: index.php?msg=added");
    exit;
}

// ================= UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supplier'])) {

    $stmt = $conn->prepare("
        UPDATE suppliers 
        SET Name=?, Phone=?, provinceCode=?, districtCode=?, sectorCode=?, cellCode=?, villageCode=?
        WHERE SupplierID=?
    ");

    $stmt->execute([
        $_POST['Name'],
        $_POST['Phone'],
        $_POST['provinceCode'],
        $_POST['districtCode'],
        $_POST['sectorCode'],
        $_POST['cellCode'],
        $_POST['villageCode'],
        $_POST['SupplierID']
    ]);

    header("Location: index.php?msg=updated");
    exit;
}

// ================= DELETE =================
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE SupplierID=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php?msg=deleted");
    exit;
}

// ================= LOAD TABLE =================
if (isset($_GET['load'])) {

    $stmt = $conn->prepare("
        SELECT s.*,
        p.provincename AS province,
        d.DistrictName AS district,
        se.SectorName AS sector,
        c.CellName AS cell,
        v.VillageName AS village
        FROM suppliers s
        LEFT JOIN provinces p ON s.provinceCode = p.provincecode
        LEFT JOIN districts d ON s.districtCode = d.DistrictCode
        LEFT JOIN sectors se ON s.sectorCode = se.SectorCode
        LEFT JOIN cells c ON s.cellCode = c.CellCode
        LEFT JOIN villages v ON s.villageCode = v.VillageCode
        ORDER BY s.SupplierID DESC
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <table id="supplierTable" class="table table-bordered table-striped table-hover">
       <thead class="table-dark">
<tr>
<th>ID</th>
<th>👤 Name</th>
<th>📞 Phone</th>
<th>🌍 Province</th>
<th>🏙 District</th>
<th>🏢 Sector</th>
<th>📍 Cell</th>
<th>🏡 Village</th>
<th>⚙️ Actions</th>
</tr>
</thead>

        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['SupplierID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td><?= $row['Phone'] ?></td>
                    <td><?= $row['province'] ?></td>
                    <td><?= $row['district'] ?></td>
                    <td><?= $row['sector'] ?></td>
                    <td><?= $row['cell'] ?></td>
                    <td><?= $row['village'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm editBtn" data-id="<?= $row['SupplierID'] ?>">✏️</button>
                        <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $row['SupplierID'] ?>">🗑️</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php exit;
}

// ================= LOAD EDIT =================
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE SupplierID=?");
    $stmt->execute([$_GET['edit']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}
?>

<?php include("../includes/layout.php"); ?>

<!DOCTYPE html>
<html>

<head>
    <title>Suppliers</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let msg = "<?= $_GET['msg'] ?>";

            if (msg === "added") {
                Swal.fire("Success!", "Supplier added successfully", "success");
            }
            if (msg === "updated") {
                Swal.fire("Updated!", "Supplier updated successfully", "success");
            }
            if (msg === "deleted") {
                Swal.fire("Deleted!", "Supplier deleted successfully", "success");
            }

        });
    </script>
<?php endif; ?>

<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0">📦 Suppliers Management</h4>
                    <small class="text-muted">Manage all your honey suppliers</small>
                </div>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    ➕ Add Supplier
                </button>
            </div>

            <div id="table"></div>

        </div>

        <!-- ================= CREATE MODAL ================= -->
        <div class="modal fade" id="createModal">
            <div class="modal-dialog">
                <div class="modal-content p-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">➕ Add Supplier</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <form id="createForm">

                            <input type="text" name="Name" class="form-control mb-2" placeholder="Name" required>
                            <input type="text" name="Phone" class="form-control mb-2" placeholder="Phone">

                            <select name="provinceCode" class="form-control mb-2">
                                <option value="">Province</option>
                                <?php
                                $provinces = $conn->query("SELECT * FROM provinces")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($provinces as $p) {
                                    echo "<option value='{$p['provincecode']}'>{$p['provincename']}</option>";
                                }
                                ?>
                            </select>

                            <select name="districtCode" class="form-control mb-2"></select>
                            <select name="sectorCode" class="form-control mb-2"></select>
                            <select name="cellCode" class="form-control mb-2"></select>
                            <select name="villageCode" class="form-control mb-2"></select>

                            <button class="btn btn-success w-100 mt-2">💾 Save Supplier</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- ================= EDIT MODAL ================= -->
        <div class="modal fade" id="editModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5>Edit Supplier</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post">

                            <div class="modal-header">
                                <h5>Edit Supplier</h5>
                            </div>

                            <div class="modal-body">

                                <input type="hidden" name="SupplierID" id="eid">

                                <input type="text" name="Name" id="ename" class="form-control mb-2" placeholder="Name">
                                <input type="text" name="Phone" id="ephone" class="form-control mb-2" placeholder="Phone">

                                <!-- Province -->
                                <select id="eprovince" name="provinceCode" class="form-control mb-2">
                                    <option value="">Select Province</option>
                                    <?php foreach ($provinces as $p): ?>
                                        <option value="<?= $p['provincecode'] ?>"><?= $p['provincename'] ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Dependent dropdowns -->
                                <select id="edistrict" name="districtCode" class="form-control mb-2">
                                    <option value="">Select District</option>
                                </select>

                                <select id="esector" name="sectorCode" class="form-control mb-2">
                                    <option value="">Select Sector</option>
                                </select>

                                <select id="ecell" name="cellCode" class="form-control mb-2">
                                    <option value="">Select Cell</option>
                                </select>

                                <select id="evillage" name="villageCode" class="form-control mb-2">
                                    <option value="">Select Village</option>
                                </select>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="update_supplier" class="btn btn-warning w-100 mt-2">
                                    ✏️ Update Supplier
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
        <?php include("../includes/footer.php"); ?>

        <!-- ================= EDIT MODAL END ================= -->

        <!-- ================= DATATABLES ================= -->
        <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            // LOAD TABLE
            function loadTable() {
                fetch("?load=1")
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById("table").innerHTML = data;

                        // reinitialize DataTable
                        if ($.fn.DataTable.isDataTable('#supplierTable')) {
                            $('#supplierTable').DataTable().destroy();
                        }

                        $('#supplierTable').DataTable({
                            pageLength: 10,
                            order: [
                                [0, "desc"]
                            ]
                        });
                    });
            }

            loadTable();

            // CREATE
            document.getElementById("createForm").addEventListener("submit", e => {
                e.preventDefault();
                let fd = new FormData(e.target);
                fd.append("action", "create");

                fetch("", {
                    method: "POST",
                    body: fd
                }).then(() => {
                    loadTable();
                    bootstrap.Modal.getInstance(document.getElementById("createModal")).hide();
                    e.target.reset();
                });
            });

            // DELETE
            document.addEventListener("click", e => {
                if (e.target.classList.contains("deleteBtn")) {
                    if (confirm("Delete this supplier?")) {
                        fetch(`?delete=${e.target.dataset.id}`).then(() => loadTable());
                    }
                }
            });

            // EDIT
            document.addEventListener("click", e => {
                if (e.target.classList.contains("editBtn")) {
                    fetch(`?edit=${e.target.dataset.id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById("eid").value = data.SupplierID;
                            document.getElementById("ename").value = data.Name;
                            document.getElementById("ephone").value = data.Phone;
                            new bootstrap.Modal(document.getElementById("editModal")).show();
                        });
                }
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const province = document.querySelector("select[name='provinceCode']");
                const district = document.querySelector("select[name='districtCode']");
                const sector = document.querySelector("select[name='sectorCode']");
                const cell = document.querySelector("select[name='cellCode']");
                const village = document.querySelector("select[name='villageCode']");

                // Province → District
                province.addEventListener("change", function() {

                    district.innerHTML = '<option value="">Loading...</option>';
                    sector.innerHTML = '<option value="">Sector</option>';
                    cell.innerHTML = '<option value="">Cell</option>';
                    village.innerHTML = '<option value="">Village</option>';

                    fetch(`../get_districts.php?province=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            district.innerHTML = '<option value="">Select District</option>';
                            data.forEach(d => {
                                district.innerHTML += `<option value="${d.DistrictCode}">${d.DistrictName}</option>`;
                            });
                        });
                });

                // District → Sector
                district.addEventListener("change", function() {

                    sector.innerHTML = '<option value="">Loading...</option>';
                    cell.innerHTML = '<option value="">Cell</option>';
                    village.innerHTML = '<option value="">Village</option>';

                    fetch(`../get_sectors.php?district=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            sector.innerHTML = '<option value="">Select Sector</option>';
                            data.forEach(s => {
                                sector.innerHTML += `<option value="${s.SectorCode}">${s.SectorName}</option>`;
                            });
                        });
                });

                // Sector → Cell
                sector.addEventListener("change", function() {

                    cell.innerHTML = '<option value="">Loading...</option>';
                    village.innerHTML = '<option value="">Village</option>';

                    fetch(`../get_cells.php?sector=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            cell.innerHTML = '<option value="">Select Cell</option>';
                            data.forEach(c => {
                                cell.innerHTML += `<option value="${c.CellCode}">${c.CellName}</option>`;
                            });
                        });
                });

                // Cell → Village
                cell.addEventListener("change", function() {

                    village.innerHTML = '<option value="">Loading...</option>';

                    fetch(`../get_villages.php?cell=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            village.innerHTML = '<option value="">Select Village</option>';
                            data.forEach(v => {
                                village.innerHTML += `<option value="${v.VillageCode}">${v.VillageName}</option>`;
                            });
                        });
                });

            });
        </script>

        <!-- ================= EDIT MODAL JS ================= -->
        <script>
            // ================= EDIT BUTTON =================
            document.addEventListener("click", async function(e) {
                if (e.target.classList.contains("editBtn")) {

                    let id = e.target.dataset.id;

                    let res = await fetch(`?edit=${id}`);
                    let data = await res.json();

                    // Fill basic fields
                    document.getElementById("eid").value = data.SupplierID;
                    document.getElementById("ename").value = data.Name;
                    document.getElementById("ephone").value = data.Phone;

                    // Set province
                    document.getElementById("eprovince").value = data.provinceCode;

                    // Load cascading dropdowns
                    await loadEditDistricts(data.provinceCode, data.districtCode);
                    await loadEditSectors(data.districtCode, data.sectorCode);
                    await loadEditCells(data.sectorCode, data.cellCode);
                    await loadEditVillages(data.cellCode, data.villageCode);

                    new bootstrap.Modal(document.getElementById("editModal")).show();
                }
            });

            // ================= CASCADE FUNCTIONS =================

            async function loadEditDistricts(provinceId, selected = null) {
                let res = await fetch(`../get_districts.php?province=${provinceId}`);
                let data = await res.json();

                let select = document.getElementById("edistrict");
                select.innerHTML = '<option value="">Select District</option>';

                data.forEach(d => {
                    let opt = document.createElement("option");
                    opt.value = d.DistrictCode;
                    opt.textContent = d.DistrictName;
                    select.appendChild(opt);
                });

                if (selected) select.value = selected;
            }

            async function loadEditSectors(districtId, selected = null) {
                let res = await fetch(`../get_sectors.php?district=${districtId}`);
                let data = await res.json();

                let select = document.getElementById("esector");
                select.innerHTML = '<option value="">Select Sector</option>';

                data.forEach(s => {
                    let opt = document.createElement("option");
                    opt.value = s.SectorCode;
                    opt.textContent = s.SectorName;
                    select.appendChild(opt);
                });

                if (selected) select.value = selected;
            }

            async function loadEditCells(sectorId, selected = null) {
                let res = await fetch(`../get_cells.php?sector=${sectorId}`);
                let data = await res.json();

                let select = document.getElementById("ecell");
                select.innerHTML = '<option value="">Select Cell</option>';

                data.forEach(c => {
                    let opt = document.createElement("option");
                    opt.value = c.CellCode;
                    opt.textContent = c.CellName;
                    select.appendChild(opt);
                });

                if (selected) select.value = selected;
            }

            async function loadEditVillages(cellId, selected = null) {
                let res = await fetch(`../get_villages.php?cell=${cellId}`);
                let data = await res.json();

                let select = document.getElementById("evillage");
                select.innerHTML = '<option value="">Select Village</option>';

                data.forEach(v => {
                    let opt = document.createElement("option");
                    opt.value = v.VillageCode;
                    opt.textContent = v.VillageName;
                    select.appendChild(opt);
                });

                if (selected) select.value = selected;
            }

            // ================= MANUAL CASCADE (EDIT ONLY) =================

            // Province → District
            document.getElementById("eprovince").addEventListener("change", function() {
                loadEditDistricts(this.value);
            });

            // District → Sector
            document.getElementById("edistrict").addEventListener("change", function() {
                loadEditSectors(this.value);
            });

            // Sector → Cell
            document.getElementById("esector").addEventListener("change", function() {
                loadEditCells(this.value);
            });

            // Cell → Village
            document.getElementById("ecell").addEventListener("change", function() {
                loadEditVillages(this.value);
            });
        </script>