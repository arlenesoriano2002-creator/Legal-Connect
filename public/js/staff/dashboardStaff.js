// Burger menu
  const burgerBtn = document.getElementById('burgerBtn');
  const sidebar = document.querySelector('aside.sidebar');
  burgerBtn.addEventListener('click', () => {
    sidebar.classList.add('active');
    burgerBtn.style.display = 'none';
  });
  sidebar.addEventListener('mouseleave', () => {
    sidebar.classList.remove('active');
    burgerBtn.style.display = 'block';
  });
// Auto-refresh data every 60 seconds
        setInterval(function() {
            // This would typically make an AJAX request to refresh the data
            console.log("Data refresh triggered");
            // In a real implementation, you would fetch updated data from the server
        }, 60000);