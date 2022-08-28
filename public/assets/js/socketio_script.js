let socket = io('http://localhost:3000');

console.log(socket);

var user_id = document.getElementById('user_id').value;
//Listen Specifically for a user (user_id)
socket.on('receiveNotificationToUser_'+user_id, (message) => {
    $('#main').prepend(`<br><span class="badge bg-primary pb-0 mb-4"><h6>`+message+`</h6></span>`);
    console.log(message);
});