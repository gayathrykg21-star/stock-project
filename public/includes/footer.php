    </main>
</div>

<script>
// Date/Time display
function updateDateTime() {
    var now = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = now.toLocaleDateString('en-IN', options);
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-IN');
}
updateDateTime();
setInterval(updateDateTime, 1000);

// Mobile sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>
</body>
</html>
