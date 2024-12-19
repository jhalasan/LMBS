// Sidebar Toggle Functions
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.querySelector(".main-content");
  const externalToggleBtn = document.getElementById("externalToggleBtn");

  if (!sidebar || !mainContent || !externalToggleBtn) return;

  // Check the current position of the sidebar using computed style
  const sidebarLeft = getComputedStyle(sidebar).left;

  if (sidebarLeft === "-250px") {
    sidebar.style.left = "0"; // Show sidebar
    mainContent.style.marginLeft = "250px"; // Shift main content to the right
    externalToggleBtn.style.opacity = "0";
    externalToggleBtn.style.visibility = "hidden";
  } else {
    sidebar.style.left = "-250px"; // Hide sidebar
    mainContent.style.marginLeft = "0"; // Reset main content margin
    externalToggleBtn.style.opacity = "1";
    externalToggleBtn.style.visibility = "visible";
  }
}

function toggleSidebarInside() {
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.querySelector(".main-content");
  const externalToggleBtn = document.getElementById("externalToggleBtn");

  if (!sidebar || !mainContent || !externalToggleBtn) return;

  sidebar.style.left = "-250px";
  mainContent.style.marginLeft = "0";
  externalToggleBtn.style.opacity = "1";
  externalToggleBtn.style.visibility = "visible";
}

// Form submission handling for adding books
document.getElementById("add-book-form")?.addEventListener("submit", (e) => {
  e.preventDefault();

  const bookID = document.getElementById("bookID")?.value.trim();
  const bookTitle = document.getElementById("bookTitle")?.value.trim();
  const bookAuthor = document.getElementById("bookAuthor")?.value.trim();

  if (!bookID || !bookTitle || !bookAuthor) {
    alert("All fields are required!");
    return;
  }

  const bookData = { bookID, bookTitle, bookAuthor };

  fetch("../php/addbook.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(bookData),
  })
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        alert("Book added successfully!");
        document.getElementById("add-book-form")?.reset();
      } else {
        alert(`Error: ${data.message}`);
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      alert("Failed to add the book. Please try again.");
    });
});

// Fetching and displaying books
document.addEventListener("DOMContentLoaded", () => {
  const bookList = document.getElementById("bookList");
  const searchInput = document.getElementById("searchInput");

  if (!bookList || !searchInput) return;

  fetchBooks();

  // Fetch and display books based on search
  searchInput?.addEventListener("keyup", () => {
    const query = searchInput.value.trim();
    const searchOption = document.querySelector('input[name="searchOption"]:checked')?.value;

    if (!query || !searchOption) {
      fetchBooks(); // Fetch all books if no query or option
      return;
    }

    fetch("../php/searchbook.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ query, searchOption }),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Failed to fetch search results.");
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          displayBooks(data.books); // Display filtered books
        } else {
          bookList.innerHTML = `
            <p class="no-results">No books found matching your query.</p>
          `;
        }
      })
      .catch((error) => {
        console.error("Error performing search:", error);
        bookList.innerHTML = `
          <p class="no-results">An error occurred while searching for books. Please try again later.</p>
        `;
      });
  });

  function fetchBooks() {
    fetch("../php/getbooks.php")
      .then((response) => {
        if (!response.ok) throw new Error("Failed to fetch books.");
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          displayBooks(data.books);
        } else {
          bookList.innerHTML = `<p style="color: #ff4d4d;">Error: ${data.message}</p>`;
        }
      })
      .catch((error) => {
        console.error("Error fetching books:", error);
        bookList.innerHTML = `<p style="color: #ff4d4d;">Failed to fetch books. Please try again later.</p>`;
      });
  }

  function displayBooks(books) {
    const table = document.createElement("table");
    table.style.width = "100%";
    table.style.borderCollapse = "collapse";
    table.style.color = "#fff";

    table.innerHTML =
      `<thead>
        <tr>
          <th style="border: 1px solid #555; padding: 10px;">Book ID</th>
          <th style="border: 1px solid #555; padding: 10px;">Title</th>
          <th style="border: 1px solid #555; padding: 10px;">Author</th>
          <th style="border: 1px solid #555; padding: 10px;">Status</th>
          <th style="border: 1px solid #555; padding: 10px;">Action</th>
        </tr>
      </thead>`;

    const tbody = document.createElement("tbody");

    Object.entries(books).forEach(([id, book]) => {
      const row = document.createElement("tr");
      const actionButton = document.createElement("button");

      actionButton.setAttribute('data-book-id', id);
      actionButton.setAttribute('data-title', book.title);
      actionButton.setAttribute('data-author', book.author);

      // Add button style based on book status
      updateButtonStatus(actionButton, book.status);

      actionButton.addEventListener("click", () => borrowBook(id, book.status, actionButton));

      row.innerHTML =
        `<td style="border: 1px solid #555; padding: 10px;">${id}</td>
        <td style="border: 1px solid #555; padding: 10px;">${book.title}</td>
        <td style="border: 1px solid #555; padding: 10px;">${book.author}</td>
        <td style="border: 1px solid #555; padding: 10px;">${book.status}</td>
        <td style="border: 1px solid #555; padding: 10px; text-align: center;"></td>`;

      row.cells[4].appendChild(actionButton);
      tbody.appendChild(row);
    });

    table.appendChild(tbody);
    bookList.innerHTML = "";
    bookList.appendChild(table);
  }

  function updateButtonStatus(actionButton, newStatus) {
    actionButton.style.color = "white";
    actionButton.style.border = "none";
    actionButton.style.padding = "5px 10px";
    actionButton.style.cursor = "pointer";
    actionButton.style.borderRadius = "5px";

    if (newStatus === "requested") {
      actionButton.textContent = "Request Pending";
      actionButton.style.backgroundColor = "#2196F3"; // Blue for requested
      actionButton.disabled = true;
    } else if (newStatus === "available") {
      actionButton.textContent = "Borrow";
      actionButton.style.backgroundColor = "#4CAF50"; // Green for available
      actionButton.disabled = false;
    } else if (newStatus === "borrowed") {
      actionButton.textContent = "Return";
      actionButton.style.backgroundColor = "#f44336"; // Red for borrowed
      actionButton.disabled = false;
    }
  }

  function borrowBook(bookID, currentStatus, actionButton) {
    if (!actionButton || !bookID) {
      console.error("Invalid arguments passed to borrowBook.");
      return;
    }

    switch (currentStatus) {
      case "available":
        actionButton.textContent = "Request Pending";
        actionButton.style.backgroundColor = "#2196F3"; // Blue for request pending
        actionButton.disabled = true;
        addToRequestQueue(bookID);
        break;
      case "borrowed":
        returnBook(bookID, actionButton);
        break;
      case "requested":
        alert("Your request is already pending.");
        break;
      default:
        console.warn(`Unknown book status: ${currentStatus}`);
        break;
    }
  }

  function returnBook(bookID, actionButton) {
    fetch("../php/bookstatus.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ bookID, newStatus: "available" }),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Failed to update book status.");
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          alert("Book returned successfully!");
          actionButton.textContent = "Borrow";
          actionButton.style.backgroundColor = "#4CAF50"; // Green for "Borrow"
          actionButton.disabled = false;
        } else {
          alert(`Error: ${data.message}`);
        }
      })
      .catch((error) => {
        console.error("Error returning book:", error);
        alert("Failed to return the book. Please try again.");
      });
  }

  function addToRequestQueue(bookID) {
    const actionButton = document.querySelector(`[data-book-id="${bookID}"]`);

    if (!actionButton) {
      console.error("No action button found for this book.");
      return;
    }

    const bookTitle = actionButton.getAttribute('data-title');
    const bookAuthor = actionButton.getAttribute('data-author');

    const requestData = {
      bookID,
      title: bookTitle,
      author: bookAuthor,
      status: "requested",
    };

    fetch("../php/addrequest.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Book request added successfully.");
        } else {
          alert(`Error: ${data.message}`);
        }
      })
      .catch((error) => {
        console.error("Error adding book request:", error);
        alert("Failed to add book request.");
      });
  }
});
