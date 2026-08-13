import {
    db,
    collection,
    addDoc,
    serverTimestamp
} from "../../firebase/firebase-config.js";


document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("portfolioContactForm");

    if (!form) {
        return;
    }


    const nameInput = document.getElementById("senderName");
    const emailInput = document.getElementById("senderEmail");
    const messageInput = document.getElementById("senderMessage");

    const userIdInput = document.getElementById("portfolioUserId");

    const submitButton = document.getElementById("sendMessageButton");

    const statusMessage = document.getElementById("contactStatus");


    form.addEventListener("submit", async function (event) {

        event.preventDefault();


        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        const message = messageInput.value.trim();

        const portfolioUserId = userIdInput.value;


        /* ---------------------------------------------
           Basic validation
        --------------------------------------------- */

        if (!name) {

            showStatus(
                "Please enter your name.",
                "error"
            );

            nameInput.focus();

            return;
        }


        if (!email) {

            showStatus(
                "Please enter your email address.",
                "error"
            );

            emailInput.focus();

            return;
        }


        if (!isValidEmail(email)) {

            showStatus(
                "Please enter a valid email address.",
                "error"
            );

            emailInput.focus();

            return;
        }


        if (!message) {

            showStatus(
                "Please enter your message.",
                "error"
            );

            messageInput.focus();

            return;
        }


        if (!portfolioUserId) {

            showStatus(
                "The portfolio owner could not be identified.",
                "error"
            );

            return;
        }


        /* ---------------------------------------------
           Disable button
        --------------------------------------------- */

        submitButton.disabled = true;

        submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm"
                  aria-hidden="true"></span>
            Sending...
        `;


        try {

            /*
             * The PHP page provides the portfolio owner's
             * email address through this hidden field.
             */

            const ownerEmailElement =
                document.getElementById("portfolioOwnerEmail");


            if (!ownerEmailElement) {

                throw new Error(
                    "Portfolio owner email is missing."
                );

            }


            const ownerEmail =
                ownerEmailElement.value.trim();


            if (!ownerEmail) {

                throw new Error(
                    "Portfolio owner email is empty."
                );

            }


            /* -----------------------------------------
               Create Firestore email document
            ----------------------------------------- */

            await addDoc(
                collection(db, "mail"),
                {

                    to: [
                        ownerEmail
                    ],

                    replyTo: email,

                    message: {

                        subject:
                            "New message from " + name +
                            " - Portify",

                        text:
                            `You received a new message through your Portify portfolio.

Name:
${name}

Email:
${email}

Message:
${message}

You can reply directly to this email to contact ${name}.`,

                        html:
                            `
<div style="
    font-family: Arial, sans-serif;
    max-width: 650px;
    margin: 0 auto;
    color: #2d2933;
">

    <div style="
        background: #6f42c1;
        padding: 25px;
        text-align: center;
        color: white;
    ">

        <h2 style="
            margin: 0;
            font-size: 24px;
        ">
            Portify
        </h2>

        <p style="
            margin: 8px 0 0;
            opacity: 0.9;
        ">
            New Portfolio Message
        </p>

    </div>


    <div style="
        padding: 30px;
        background: #ffffff;
    ">

        <h3 style="
            margin-top: 0;
            color: #6f42c1;
        ">
            You received a new message
        </h3>


        <p>
            Someone has contacted you through your
            Portify portfolio.
        </p>


        <div style="
            margin-top: 25px;
            padding: 20px;
            background: #f7f5fa;
            border-radius: 8px;
        ">

            <p>
                <strong>Name:</strong><br>
                ${escapeHtml(name)}
            </p>


            <p>
                <strong>Email:</strong><br>
                ${escapeHtml(email)}
            </p>


            <p>
                <strong>Message:</strong><br>
                ${escapeHtml(message).replace(/\n/g, "<br>")}
            </p>

        </div>


        <p style="
            margin-top: 25px;
            color: #777;
            font-size: 13px;
        ">
            You can reply directly to this email
            to respond to ${escapeHtml(name)}.
        </p>

    </div>


    <div style="
        padding: 18px;
        text-align: center;
        background: #f7f5fa;
        color: #888;
        font-size: 12px;
    ">

        Powered by Portify

    </div>

</div>
`

                    },

                    portfolio_user_id:
                        portfolioUserId,

                    sender_name:
                        name,

                    sender_email:
                        email,

                    sender_message:
                        message,

                    created_at:
                        serverTimestamp(),

                    read:
                        false

                }
            );


            /* -----------------------------------------
               Success
            ----------------------------------------- */

            showStatus(
                "Your message has been sent successfully!",
                "success"
            );


            form.reset();


        } catch (error) {

            console.error(
                "Firebase Contact Error:",
                error
            );


            showStatus(
                "Sorry, your message could not be sent. Please try again.",
                "error"
            );


        } finally {

            submitButton.disabled = false;

            submitButton.innerHTML = `
                <i class="bi bi-send"></i>
                Send Message
            `;

        }

    });


    /* ---------------------------------------------
       Email validation
    --------------------------------------------- */

    function isValidEmail(email) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    }


    /* ---------------------------------------------
       Status message
    --------------------------------------------- */

    function showStatus(message, type) {

        if (!statusMessage) {
            alert(message);
            return;
        }


        statusMessage.className =
            "portfolio-contact-alert " + type;


        statusMessage.innerHTML = `
            <i class="bi ${type === "success"
                ? "bi-check-circle"
                : "bi-exclamation-circle"
            }"></i>

            <span>${message}</span>
        `;


        statusMessage.style.display = "flex";


        if (type === "success") {

            setTimeout(function () {

                statusMessage.style.display = "none";

            }, 5000);

        }

    }


    /* ---------------------------------------------
       Prevent HTML injection in email
    --------------------------------------------- */

    function escapeHtml(value) {

        return value
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    }

});