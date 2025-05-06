<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antibiotic Release Form</title>
</head>
<body>
    <h1>Antibiotic Release Form</h1>
    <form action="process-antibiotic.php" method="POST">
        <label for="antibiotic_name">Antibiotic Name:</label>
        <input type="text" id="antibiotic_name" name="antibiotic_name" required><br><br>

        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject"><br><br>

        <label for="dosage">Dosage (separate with commas):</label>
        <input type="text" id="dosage" name="dosage" required><br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
