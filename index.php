<?php
// Connect to database.
$db = require_once  '../../config/connect.php';
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Input Validations
unset($errorMessage);
$errorMessage = [];
if (isset($_POST['name'])) {
    // Validating name field.
    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);
    }
    if (strlen($name) > 120) {
        $errorMessage['name'] = 'The Name should not be longer than 120 characters.';
    }
    if (strlen($name) == 0) {
        $errorMessage['name'] = 'Name is required.';
    }
    // Validating location field.
    if (isset($_POST['location'])) {
        $location = trim($_POST['location']);
    }
    if (strlen($location) > 120) {
        $errorMessage['location'] = 'The Location should not be longer than 120 characters.';
    }
    if (strlen($location) == 0) {
        $errorMessage['location'] = 'Location is required';
    }
    // Saving input in database when validation passes.
    if (empty($errorMessage)) {
        try {

            // Prepared INSERT SQL statement.
            $sql = "INSERT INTO customers (name, location) VALUES (:name, :location)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ":name" => $name,
                ":location" => $location
            ]);
            echo $stmt->rowCount() . " row/s of data affected.";
        } catch (PDOException $e) {
            echo "Saving data did not work: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple CRUD Web App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <div class="m-5">
        <legend>List of customers</legend>
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Location</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT name, location FROM customers";
                foreach ($db->query($sql) as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row[0]) . "</td>";
                    echo "<td>" . htmlspecialchars($row[1]) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="m-5">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <legend>Create new customer</legend>
            <div class="mb-3">
                <label for="inputName" class="form-label">Name</label>
                <input type="text" name="name" class="form-control" id="inputName" maxlength="120">
                <div id="nameRequired" class="form-text text-danger"><?= $errorMessage['name'] ?? ''; ?></div>
            </div>
            <div class="mb-3">
                <label for="inputLocation" class="form-label">Location</label>
                <input type="text" name="location" class="form-control" id="inputLocation" maxlength="120">
                <div id="locationRequired" class="form-text text-danger"><?= $errorMessage['location'] ?? ''; ?></div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>