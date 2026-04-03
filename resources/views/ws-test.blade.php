<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Test</title>
</head>
<body>
    <h1>WebSocket Test</h1>
    <input type="text" id="to_user_id" placeholder="To User ID">
    <input type="text" id="message" placeholder="Your Message">
    <button onclick="send()">Send</button>

    <ul id="messages"></ul>

    <script>
        const userId = "{{ $userId }}";
        const socket = new WebSocket(`ws://hrms.quicklease.ae:8081?user_id=${userId}`);

        socket.onopen = function() {
            console.log("✅ Connected to WebSocket server");
        };

       socket.onmessage = function(event) {
            const data = JSON.parse(event.data);
            const li = document.createElement("li");

            if (data.type === "notification") {
                li.innerText = `From user ${data.from_user_id}: ${data.message}`;
            } else {
                li.innerText = `Raw: ${event.data}`;
            }

            document.getElementById("messages").appendChild(li);
        };

        function send() {
            const msg = document.getElementById("message").value;
            const toUserId = document.getElementById("to_user_id").value;

            socket.send(JSON.stringify({
                to_user_id: toUserId,
                message: msg
            }));
        }
    </script>
</body>
</html>
