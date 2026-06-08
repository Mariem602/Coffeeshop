let isLoggedIn = false;
let navbar = document.querySelector('.navbar');

document.querySelector('#menu-icon').onclick = () => {
  navbar.classList.toggle('active');
}

window.onscroll = () => {
  navbar.classList.remove('active');
}

let header = document.querySelector('header');
window.addEventListener('scroll', () => {
  header.classList.toggle('shadow', window.scrollY > 0);
});

window.addEventListener('DOMContentLoaded', async () => {
    // Check login
    try {
        const res  = await fetch('/touskie/check_session.php');
        const data = await res.json();
        console.log('full data:', data);
        isLoggedIn = data.loggedIn;

        if (isLoggedIn) {
            const pending = sessionStorage.getItem('pendingItem');
            if (pending) {
                sessionStorage.removeItem('pendingItem');
                const { name, price } = JSON.parse(pending);
                openPaymentModal(name, price);
            }
        }
    } catch (err) {
        console.error('Session check failed:', err);
    }

    // Payment form validation
    const payForm = document.querySelector('#payModal form');
    if (payForm) {
        payForm.addEventListener('submit', function(e) {

            const cardNumber = document.querySelector('[name="card_number"]').value.replace(/\s/g, '');
            const expiry     = document.querySelector('[name="expiry"]').value;
            const cvv        = document.querySelector('[name="cvv"]').value;

            if (!/^\d{16}$/.test(cardNumber)) {
                alert('Please enter a valid 16-digit card number.');
                return;
            }

            if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                alert('Please enter expiry in MM/YY format.');
                return;
            }
            const [month, year] = expiry.split('/').map(Number);
            const expDate = new Date(2000 + year, month - 1);
            if (month < 1 || month > 12) {
                alert('Please enter a valid expiry month.');
                return;
            }
            if (expDate < new Date()) {
                alert('Your card has expired.');
                return;
            }

            if (!/^\d{3,4}$/.test(cvv)) {
                alert('Please enter a valid CVV (3 or 4 digits).');
                return;
            }

           const formData = new FormData(this);

            fetch('payment.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                document.getElementById('payModal').style.display = 'none';
            })
            .catch(err => console.error('Payment error:', err));
                    });
    }
    // Load reviews
    loadReviews();
});



// ===== Cart / Payment =====
function buyNow(name, price) {
  console.log('isLoggedIn:', isLoggedIn);
  if (!isLoggedIn) {
    sessionStorage.setItem('pendingItem', JSON.stringify({ name, price }));
    document.getElementById('loginModal').style.display = 'flex';
    return;
  }

  openPaymentModal(name, price);
}

function openPaymentModal(name, price) {
  document.getElementById('input-name').value  = name;
  document.getElementById('input-price').value = price;

  document.getElementById('order-summary').innerHTML = `
    <div style="display:flex; justify-content:space-between;">
      <span>${name}</span>
      <strong>${price.toFixed(3)} TND</strong>
    </div>
  `;

  document.getElementById('payModal').style.display = 'flex';
}
// ===== Reviews =====
async function loadReviews() {
    try {
        const res     = await fetch('/touskie/getreviews.php');
        const reviews = await res.json();

        console.log('reviews fetched:', reviews); // check browser console

        const container = document.getElementById('reviews-container');
        const moreBtn   = document.getElementById('more-btn');

        if (reviews.length === 0) {
            container.innerHTML = '<p style="text-align:center">No reviews yet.</p>';
            return;
        }

        reviews.slice(0, 3).forEach(review => {
            container.appendChild(createReviewBox(review));
        });

        if (reviews.length > 3) {
            moreBtn.style.display = 'inline-block';
            moreBtn.addEventListener('click', () => {
                reviews.slice(3, ).forEach(review => {
                    container.appendChild(createReviewBox(review));
                });
                moreBtn.style.display = 'none';
            });
        }

    } catch (err) {
        console.error('Error loading reviews:', err);
    }
}

function createReviewBox(review) {
    const box = document.createElement('div');
    box.classList.add('box');
    box.innerHTML = `
        <div class="stars">${renderStars(review.rating)}</div>
        <p>${review.comment}</p>
        <h2>${review.full_name}</h2>
    `;
    return box;
}

function renderStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += i <= rating 
            ? `<i class='bx bxs-star'></i>` 
            : `<i class='bx bx-star'></i>`;
    }
    return stars;
}

