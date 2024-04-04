document.addEventListener('DOMContentLoaded', init);



const BASE_URI = 'http://localhost:8000/kahuna/api/';
let products = [];
let rproducts = [];
let SupportTicket = [];


function init() {
    checkAndRedirect('product', loadProducts);
}

async function showView(view) {
    if (view) {
        return fetch(`${view}.html`)
            .then(res => res.text())
            .then(html => document.getElementById('mainContent').innerHTML = html);
    }
    return null;
}

async function isValidToken(token, user, cb) {
    return fetch(`${BASE_URI}token`, {
        headers: {
            'X-Api-Key': token,
            'X-Api-User': user
        }
    })
        .then(res => res.json())
        .then(res => cb(res.data.valid));
}

function getFormData(object) {
    const formData = new FormData();
    Object.keys(object).forEach(key => formData.append(key, object[key]));
    return formData;
}

function checkAndRedirect(redirect = null, cb = null) {
    let token = localStorage.getItem("kahuna_token");

    if (!token) {
        showView('login').then(() => bindLogin(redirect, cb));
    } else {
        let user = localStorage.getItem("kahuna_user");
        isValidToken(token, user, (valid) => {
            if (valid) {
                showView(redirect).then(cb);
            } else {
                showView('login').then(() => bindLogin(redirect, cb));
            }
        });
    }
}


function bindLogin(redirect, cb) {
    document.getElementById('loginForm').addEventListener('submit', (evt) => {
        evt.preventDefault();
        fetch(`${BASE_URI}login`, {
            mode: 'cors',
            method: 'POST',
            body: new FormData(document.getElementById('loginForm'))
        })
            .then(res => res.json())
            .then(res => {
                localStorage.setItem('kahuna_token', res.data.token);
                localStorage.setItem('kahuna_user', res.data.user);
                localStorage.setItem('kahuna_level', res.data.accessLevel);
                showView(redirect).then(cb);
            })
            .catch(err => showMessage(err, 'danger'));
    });
}

// This section will handle the product section
function loadProducts() {
    fetch(`${BASE_URI}product`, {
        mode: 'cors',
        method: 'GET',
        headers: {
            'X-Api-Key': localStorage.getItem("kahuna_token"),
            'X-Api-User': localStorage.getItem("kahuna_user")
        }
    })
    .then(res => res.json())
    .then(res => {
        products = res.data;
        displayProducts();
        bindAddProduct(); 
        if (localStorage.getItem("kahuna_level") === 'admin') {
            // Show the admin tab if the user is an admin
            document.getElementById('adminTab').style.display = 'block';
             loadTicket()
             displayTickets()
             
        } 
    })
    .catch(err => console.error(err));
};



function displayProducts() {

    if (localStorage.getItem("kahuna_level") !== 'admin') {
        document.getElementById('productForm').style.display = 'none';
    }

    let html = '';
    if (products.length === 0) {
      html = '<p>You have no products yet!</p>';
    } else {
      html = '<table><thead>';
      html += '<tr><th>Serial</th><th>Name</th><th>Warranty Length</th><th>Submit a ticket</th></tr>';
      html += '</thead><tbody>';
      for (const product of products) {
        html += '<tr>';
        html += `<td>${product.serial}</td>`;
        html += `<td>${product.name}</td>`;
        html += `<td>${product.warrantyLength}</td>`;
  
        // Add a table cell for the button
        html += `<td><button class="btn btn-primary" type="button" style="margin: 8px;">Submit</button></td>`;
  
        html += '</tr>';
      }
  
      html += '</tbody></table>';
    }
    document.getElementById('productList').innerHTML = html;
  
    // Now that the buttons are part of the document, attach event listeners
    document.querySelectorAll('.btn.btn-primary').forEach(button => {
        button.addEventListener('click', (event) => {
          event.preventDefault(); // Prevent the default form submission behavior
          loadTicket();
          // Use showView to load ticket.html into the mainContent div
          showView('ticket').then(() => {
            bindAddTicket();
            // If you need to bind any JavaScript or event listeners to the newly loaded content,
            // this would be a good place to do it.
          });
        });
      });

    }


function bindAddProduct() {
    document.getElementById('productForm').addEventListener('submit', (evt) => {
        evt.preventDefault();
        productData = new FormData(document.getElementById('productForm'));
        fetch(`${BASE_URI}product`, {
            mode: 'cors',
            method: 'POST',
            headers: {
                'X-Api-Key': localStorage.getItem("kahuna_token"),
                'X-Api-User': localStorage.getItem("kahuna_user")
            },   
            body: productData
        })

            .then(loadProducts)
            .catch(err => console.error(err));
    });

};

//Transaction

function loadTransaction() {
    fetch(`${BASE_URI}product/buy`, {
        mode: 'cors',
        method: 'GET',
        headers: {
            'X-Api-Key': localStorage.getItem("kahuna_token"),
            'X-Api-User': localStorage.getItem("kahuna_user")
        }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(res => {
        rproducts = res.data;
        displayRegisteredProducts(); // This function name should match the function intended for display.
        bindAddRegisteredProduct();
    })
    .catch(err => {
        console.error(err);
        document.getElementById('rproductList').innerHTML = `<p>Error loading transactions: ${err.message}</p>`;
    });
}

function displayRegisteredProducts() {
    let html = '';
    if (rproducts.length === 0) {
        html = '<p>You have no registered products yet!</p>';
    } else {
        html = '<table><thead>';
        html += '<tr><th>Product ID</th><th>Warranty Start Date</th><th>Warranty End Date</th><th>Purchase Date</th></tr>';
        html += '</thead><tbody>';
        for (const product of rproducts) {
            html += '<tr>';
            html += `<td>${product.productId}</td>`; // Make sure these keys match your JSON structure.
            html += `<td>${new Date(product.warranty_start_date).toLocaleDateString()}</td>`; // Format date.
            html += `<td>${new Date(product.warranty_end_date).toLocaleDateString()}</td>`; // Format date.
            html += `<td>${new Date(product.purchase_date).toLocaleDateString()}</td>`; // Format date.
            html += '</tr>';
        }
        html += '</tbody></table>';
    }
    document.getElementById('rproductList').innerHTML = html; // Ensure this ID exists in your HTML.
}

function bindAddRegisteredProduct() {
    document.getElementById('registerproduct').addEventListener('submit', (evt) => {
        evt.preventDefault();
        productData = new FormData(document.getElementById('registerproduct'));
        fetch(`${BASE_URI}product/buy`, {
            mode: 'cors',
            method: 'POST',
            headers: {
                'X-Api-Key': localStorage.getItem("kahuna_token"),
                'X-Api-User': localStorage.getItem("kahuna_user")
            },   
            body: productData
        })

        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok.');
            return response.json();
        })
        .then(data => {
            console.log(data); // or any logic to handle the response
            loadTransaction();
        })
        .catch(err => console.error(err));
        
    });

};
//End of handling transaction.


//Handling Replies


function loadReply() {
    fetch(`${BASE_URI}ReplyTicket`, {
        mode: 'cors',
        method: 'GET',
        headers: {
            'X-Api-Key': localStorage.getItem("kahuna_token"),
            'X-Api-User': localStorage.getItem("kahuna_user")
        }
    })
    .then(res => res.json())
    .then(res => {
        ReplyTicket = res.data; // Assign the response data to SupportTicket
        displayReply(); 
        bindAddReply();
    })
    .catch(err => {
        console.error(err);
        document.getElementById('reply').innerHTML = `<p>Error loading replies: ${err.message}</p>`;
    });
}

function displayReply() {
    let html = '';
    if (ReplyTicket.length === 0) {
        html = '<p>No Replies so far!</p>';
    } else {
        html = '<table><thead>';
        html += '<tr><th>Ticket ID</th><th>Description</th></tr>';
        html += '</thead><tbody>';
        for (const ticket of ReplyTicket) { // Iterate over SupportTicket instead of tickets
            html += '<tr>';
            html += `<td>${ticket.ticket_id}</td>`;
            html += `<td>${ticket.description}</td>`;
            if (localStorage.getItem("kahuna_level") === 'admin') {
                html += `<td><button class="btn btn-danger" type="button" style="margin: 8px;">Reply</button></td>`;
            } else {
                html += `<td></td>`;
            }            html += '</tr>';
        }
        html += '</tbody></table>';
    }
    document.getElementById('replyList').innerHTML = html;
}


function bindAddReply() {
    document.getElementById('reply').addEventListener('submit', (evt) => {
        evt.preventDefault();
        productData = new FormData(document.getElementById('reply'));
        fetch(`${BASE_URI}ReplyTicket`, {
            mode: 'cors',
            method: 'POST',
            headers: {
                'X-Api-Key': localStorage.getItem("kahuna_token"),
                'X-Api-User': localStorage.getItem("kahuna_user")
            },   
            body: productData
        })

        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok.');
            return response.json();
        })
        .then(data => {
            loadReply();
        })
        .catch(err => console.error(err));
        
    });

};

//End of handling replies.

//Handling Support Ticket

function loadTicket() {
    fetch(`${BASE_URI}SupportTicket`, {
        mode: 'cors',
        method: 'GET',
        headers: {
            'X-Api-Key': localStorage.getItem("kahuna_token"),
            'X-Api-User': localStorage.getItem("kahuna_user")
        }
    })
    .then(res => res.json())
    .then(res => {
        SupportTicket = res.data; // Assign the response data to SupportTicket
        displayTickets();
        
    })
    .catch(err => {
        console.error(err);
        document.getElementById('ticket').innerHTML = `<p>Error loading tickets: ${err.message}</p>`;
    });
       
}

function displayTickets() {
    let html = '';
    if (SupportTicket.length === 0) {
        html = '<p>You have no tickets yet!</p>';
    } else {
        html = '<table><thead>';
        html += '<tr><th>Name</th><th>Description</th></tr>';
        html += '</thead><tbody>';
        for (const ticket of SupportTicket) { // Iterate over SupportTicket instead of tickets
            html += '<form>'; // Start form tag
            html += '<tr>';
            html += `<td>${ticket.name}</td>`;
            html += `<td>${ticket.description}</td>`;
            html += `<input type="hidden" name="ticketId" value="${ticket.id}">`; // Hidden field for ticket ID
            if (localStorage.getItem("kahuna_level") === 'admin') {
                html += `<td><button class="btn btn-danger" type="button" style="margin: 8px;">Reply</button></td>`;
            } else {
                html += `<td></td>`;
            }
            html += '</tr>';
            html += '</form>'; // End form tag
        }
        html += '</tbody></table>';
    }
    document.getElementById('ticketList').innerHTML = html;
    
    // Now that the buttons are part of the document, attach event listeners
    document.querySelectorAll('.btn.btn-danger').forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent the default form submission behavior
            loadReply();
            // Use showView to load ticket.html into the mainContent div
            showView('reply').then(() => {
                // If you need to bind any JavaScript or event listeners to the newly loaded content,
                // this would be a good place to do it.
            });
        });
    });
}




function bindAddTicket() {
    document.getElementById('ticket').addEventListener('submit', (evt) => {
        evt.preventDefault();
        productData = new FormData(document.getElementById('ticket'));
        fetch(`${BASE_URI}SupportTicket`, {
            mode: 'cors',
            method: 'POST',
            headers: {
                'X-Api-Key': localStorage.getItem("kahuna_token"),
                'X-Api-User': localStorage.getItem("kahuna_user")
            },   
            body: productData
        })

        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok.');
            return response.json();
        })
        .then(data => {
            console.log(data); // or any logic to handle the response
            loadTicket();
        })
        .catch(err => console.error(err));
        
    });

};

//Registering a User

function registerUser() {
    showView('register').then(() => {
        document.getElementById('registerForm').addEventListener('submit', (evt) => {
            evt.preventDefault();
            fetch(`${BASE_URI}user`, {
                mode: 'cors',
                method: 'POST',
                body: new FormData(document.getElementById('registerForm'))
            })
                .then(showView('login').then(() => bindLogin('home', loadProducts)))
                .catch(err => showMessage(err, 'danger'));
        });
    });
}
function logout() {
    //Clear local storage
    localStorage.removeItem("kahuna_token");
    localStorage.removeItem("kahuna_user");
    localStorage.removeItem("kahuna_level");

    //Redirect to the login page
    window.location.href = 'index.html';
}

//Below Handles Navbar





