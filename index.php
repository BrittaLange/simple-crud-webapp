<?php
// Connect to database.
$db = require_once  '../../config/connect.php';
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Variable declaration.
$update = false;
$id = -1;
$messageType = '';
// Deleting a customer dataset.
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $sql = "DELETE FROM customers WHERE id = $id";
        $affectedRows = $db->exec($sql);
        echo $affectedRows . " datasets deleted.";
        $message = 'Your data has been deleted.';
        $messageType = 'success';
    } catch (PDOException $e) {
        echo "Deleting dataset failed. " . $e->getMessage();
    }
}
// Start edit mode.
if (isset($_GET['edit'])) {
    try {
        $update = true;
        $id = $_GET['edit'];
        $sql = "SELECT name, location FROM customers WHERE id = $id";
        $result = $db->query($sql);
        if ($result !== false) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $name = $row['name'];
                $location = $row['location'];
            }
        }
    } catch (PDOException $e) {
        echo "Editing failed. " . $e->getMessage();
    }
}
// Input Validations
$errorMessage = [];
unset($errorMessage);
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
    if (empty(($errorMessage)) && (isset($_POST['save']))) {
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
    // Updating a customer.
    if (isset($_POST['update'])) {
        try {
            $id = $_POST['id'];
            $sql = "UPDATE customers SET name = :name, location = :location WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':location' => $location,
                ':id' => $id
            ]);
            if ($stmt->rowCount() > 0) {
                $message = "Your changes have been saved.";
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            echo "Update operation failed. " . $e->getMessage();
            $message = "Update operation failed. Contact your admin.";
            $messageType = 'danger';
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
    <?php if (!(empty($message))): ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?php 
                    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
                    unset($message);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <legend>List of customers</legend>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Location</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Retrieve and display all customers in a table.
                        $sql = "SELECT id, name, location FROM customers";
                        foreach ($db->query($sql) as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo '<td>';
                            echo '<a href="index.php?edit=';
                            echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '" class="btn btn-info me-2" type="button">Edit</a>';
                            echo '<a href="index.php?delete=';
                            echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '" class="btn btn-danger" type="button">Delete</a>';
                            echo '</td>';
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <?php if ($update == true): ?>
                        <legend>Edit customer</legend>
                    <?php else: ?>
                        <legend>Create new customer</legend>
                    <?php endif; ?>
                    <input type="text" hidden name="id" value="<?php echo $id ?>">
                    <div class="mb-3">
                        <label for="inputName" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="inputName" maxlength="120" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div id="nameRequired" class="form-text text-danger"><?= $errorMessage['name'] ?? ''; ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="inputLocation" class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" id="inputLocation" maxlength="120" value="<?= htmlspecialchars($location ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div id="locationRequired" class="form-text text-danger"><?= $errorMessage['location'] ?? ''; ?></div>
                    </div>
                    <?php if ($update == true): ?>
                        <button type="submit" class="btn btn-info" name="update">Update</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary" name="save">Save</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>