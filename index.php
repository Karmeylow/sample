<!-- ✅ FOLDER STRUCTURE (USE THIS EXACT SETUP)
todolist_app/
│ index.html
│ db.sql
│
├── api/
│     tasks.php
│
└── assets/
      styles.css
      app.js

✅ 1. index.html (updated with UI design + icons + layout)

Create index.html:

<!DOCTYPE html>
<html>
<head>
  <title>Todo App</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="container">
    <h1>TODO's</h1>

    <div class="add-box">
      <input id="taskInput" placeholder="Add a new todo">
      <button id="addBtn">+</button>
    </div>

    <h2>ACTIVE</h2>
    <ul id="activeList"></ul>

    <div class="completed-header" onclick="toggleCompleted()">
      <h2>COMPLETED</h2>
      <span id="arrow">▼</span>
    </div>

    <ul id="completedList" class="hidden"></ul>
  </div>

  <script src="assets/app.js"></script>
</body>
</html>

✅ 2. styles.css (purple theme + card layout)

Create assets/styles.css:

body {
  font-family: Arial;
  background: linear-gradient(135deg, #7b2ff7, #f107a3);
  margin: 0;
  padding: 40px;
  color: white;
}

.container {
  max-width: 450px;
  margin: auto;
  background: white;
  padding: 20px;
  color: black;
  border-radius: 12px;
}

.add-box {
  display: flex;
  gap: 10px;
}

.add-box input {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.add-box button {
  width: 50px;
  font-size: 25px;
  background: #7b2ff7;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

ul {
  list-style: none;
  padding: 0;
}

li {
  background: #f3f3f3;
  margin: 8px 0;
  padding: 10px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

li.completed {
  text-decoration: line-through;
  border-left: 5px solid green;
}

.task-left {
  display: flex;
  gap: 10px;
  align-items: center;
}

.star {
  cursor: pointer;
  font-size: 20px;
}

.star.active {
  color: gold;
}

.buttons button {
  margin-left: 8px;
  cursor: pointer;
}

.completed-header {
  display: flex;
  justify-content: space-between;
  cursor: pointer;
}

.hidden {
  display: none;
}

✅ 3. app.js (ADD, EDIT, DELETE, COMPLETE, FAVORITE, SORTING)

Create assets/app.js:

document.getElementById("addBtn").onclick = addTask;

let showCompleted = false;

function toggleCompleted() {
  showCompleted = !showCompleted;
  document.getElementById("completedList").classList.toggle("hidden");
}

async function loadTasks() {
  let r = await fetch("api/tasks.php");
  let tasks = await r.json();

  const active = document.getElementById("activeList");
  const completed = document.getElementById("completedList");

  active.innerHTML = "";
  completed.innerHTML = "";

  tasks.sort((a, b) => b.favorite - a.favorite);

  tasks.forEach(t => {
    let li = document.createElement("li");

    li.className = t.completed == 1 ? "completed" : "";

    li.innerHTML = `
      <div class="task-left">
        <input type="checkbox" ${t.completed == 1 ? "checked" : ""} onchange="toggleComplete(${t.id})">
        <span>${t.name}</span>
      </div>

      <div class="buttons">
        <span class="star ${t.favorite == 1 ? "active" : ""}" onclick="toggleFavorite(${t.id})">★</span>
        <button onclick="editTask(${t.id}, '${t.name}')">✎</button>
        <button onclick="deleteTask(${t.id})">🗑</button>
      </div>
    `;

    if (t.completed == 1) completed.appendChild(li);
    else active.appendChild(li);
  });
}

async function addTask() {
  let name = document.getElementById("taskInput").value;

  await fetch("api/tasks.php", {
    method: "POST",
    body: new URLSearchParams({ action: "add", name })
  });

  document.getElementById("taskInput").value = "";
  loadTasks();
}

async function deleteTask(id) {
  await fetch("api/tasks.php", {
    method: "POST",
    body: new URLSearchParams({ action: "delete", id })
  });
  loadTasks();
}

async function toggleComplete(id) {
  await fetch("api/tasks.php", {
    method: "POST",
    body: new URLSearchParams({ action: "toggleComplete", id })
  });
  loadTasks();
}

async function toggleFavorite(id) {
  await fetch("api/tasks.php", {
    method: "POST",
    body: new URLSearchParams({ action: "toggleFavorite", id })
  });
  loadTasks();
}

function editTask(id, oldName) {
  let newName = prompt("Edit task:", oldName);
  if (!newName) return;

  fetch("api/tasks.php", {
    method: "POST",
    body: new URLSearchParams({ action: "edit", id, name: newName })
  }).then(loadTasks);
}

loadTasks();

✅ 4. tasks.php (FULL CRUD + FAVORITE + COMPLETE)

Create api/tasks.php:

<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "todo_exam");

$action = $_POST["action"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $r = $conn->query("SELECT * FROM tasks ORDER BY favorite DESC, id DESC");
    echo json_encode($r->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($action == "add") {
    $name = $_POST["name"];
    $conn->query("INSERT INTO tasks (name, completed, favorite) VALUES ('$name', 0, 0)");
}

if ($action == "delete") {
    $id = $_POST["id"];
    $conn->query("DELETE FROM tasks WHERE id=$id");
}

if ($action == "edit") {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $conn->query("UPDATE tasks SET name='$name' WHERE id=$id");
}

if ($action == "toggleComplete") {
    $id = $_POST["id"];
    $conn->query("UPDATE tasks SET completed = 1 - completed WHERE id=$id");
}

if ($action == "toggleFavorite") {
    $id = $_POST["id"];
    $conn->query("UPDATE tasks SET favorite = 1 - favorite WHERE id=$id");
}

echo json_encode(["status" => "ok"]);
?>

✅ 5. db.sql (UPDATED WITH FAVORITE COLUMN)

Create db.sql:

CREATE DATABASE todo_exam;
USE todo_exam;

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  completed TINYINT(1),
  favorite TINYINT(1)
);

🚀 HOW TO RUN (FOR A 15-YEAR-OLD)
✔ Step 1: Install XAMPP

Start Apache + MySQL

✔ Step 2: Move project folder to:
C:\xampp\htdocs\todolist_app\

✔ Step 3: Import database

Open browser → go to:

http://localhost/phpmyadmin/


Click Import → choose db.sql → Click GO

✔ Step 4: Run the app

Open browser:

http://localhost/todolist_app/ -->