<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management - Admin</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
        .btn { padding: 5px 10px; margin: 2px; border: none; border-radius: 3px; cursor: pointer; }
        .approve-btn { background: #28a745; color: white; }
        .reject-btn { background: #dc3545; color: white; }
        .delete-btn { background: #6c757d; color: white; }
        .dashboard-link { 
            background: #007bff; 
            color: white; 
            padding: 10px 15px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block; 
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <a href="admin_dashboard.php" class="dashboard-link">← Back to Admin Dashboard</a>
    <h1>Book Approval Management</h1>
    <div id="message"></div>
    
    <table id="booksTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Genre</th>
                <th>Status</th>
                <th>PDF</th>
                <th>Submit Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script>
        const API_URL = '../controller/crud.php';

        $(document).ready(function() {
            loadBooks();
        });

        function loadBooks() {
            $.ajax({
                url: API_URL,
                method: 'GET',
                data: { action: 'read'},
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const books = response.data;
                        const tbody = $('#booksTable tbody');
                        tbody.empty();
                        
                        books.forEach(book => {
                            const statusClass = book.status.toLowerCase();
                            const statusText = book.status.charAt(0).toUpperCase() + book.status.slice(1);
                            const pdfLink = book.pdf_path ? 
                                `<a href="download.php?id=${book.id}" class="download-btn">Download PDF</a>` : 
                                'N/A';
                            
                            let actions = '';
                            if (book.status === 'pending') {
                                actions = `
                                    <button class="btn approve-btn" onclick="approveBook(${book.id})">Approve</button>
                                    <button class="btn reject-btn" onclick="rejectBook(${book.id})">Reject</button>
                                `;
                            } else {
                                actions = `<button class="btn delete-btn" onclick="deleteBook(${book.id})">Delete</button>`;
                            }
                            
                            const row = `
                                <tr>
                                    <td>${book.id}</td>
                                    <td>${book.title}</td>
                                    <td>${book.author}</td>
                                    <td>${book.publisher}</td>
                                    <td>${book.genre}</td>
                                    <td><span class="${statusClass}">${statusText}</span></td>
                                    <td>${pdfLink}</td>
                                    <td>${book.publish_date || book.created_at}</td>
                                    <td>${actions}</td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        showMessage('Failed to load books.', 'error');
                    }
                }
            });
        }

        function approveBook(id) {
            if (!confirm('Approve this book?')) return;
            
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { action: 'approve_book', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showMessage(response.message, 'success');
                        loadBooks();
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        }

        function rejectBook(id) {
            if (!confirm('Reject this book? The PDF file will be deleted.')) return;
            
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { action: 'reject_book', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showMessage(response.message, 'success');
                        loadBooks();
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        }

        function deleteBook(id) {
            if (!confirm('Permanently delete this book?')) return;
            
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showMessage(response.message, 'success');
                        loadBooks();
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        }

        function showMessage(msg, type) {
            $('#message').html(`<p style="color: ${type === 'success' ? 'green' : 'red'}">${msg}</p>`);
            setTimeout(() => { $('#message').html(''); }, 5000);
        }
    </script>
</body>
</html>
