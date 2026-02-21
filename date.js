function showSection(id) {
      let sections = document.querySelectorAll('.section');
      sections.forEach(sec => sec.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      let buttons = document.querySelectorAll('.card');
      buttons.forEach(btn => btn.classList.remove('active-button'));
      event.target.closest('.card').classList.add('active-button');
    }

    document.querySelectorAll('.update').forEach(button => {
      button.addEventListener('click', function () {
        const row = this.closest('tr');
        
        if (this.classList.contains('save')) {
          const inputs = row.querySelectorAll('input');
          
          const originalData = {
            username: row.getAttribute('data-username'),
            category: row.getAttribute('data-category'),
            expense_name: row.getAttribute('data-expense_name'),
            amount: row.getAttribute('data-amount'),
            date: row.getAttribute('data-date'),
            expenseAddedDate: row.getAttribute('data-expenseAddedDate')
          };
          
          const updatedData = {
            original_username: originalData.username,
            original_category: originalData.category,
            original_expense_name: originalData.expense_name,
            original_amount: originalData.amount,
            original_date: originalData.date,
            original_expenseAddedDate: originalData.expenseAddedDate,
            
            category: inputs[0].value,
            expense_name: inputs[1].value,
            amount: inputs[2].value,
            date: inputs[3].value
          };

          fetch('update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updatedData)
          })
          .then(res => res.text())
          .then(response => {
            alert(response);
            location.reload();
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error updating expense: ' + error);
          });
          
        } else {
          const cells = row.querySelectorAll('td');
          for (let i = 0; i < 4; i++) {
            const value = cells[i].innerText.replace('₹', '');
            cells[i].innerHTML = `<input type="text" value="${value}" />`;
          }

          this.textContent = "Save";
          this.classList.add("save");
        }
      });
    });

    document.querySelectorAll('.delete').forEach(button => {
    button.addEventListener('click', function () {
        if (confirm('Are you sure you want to delete this expense?')) {
            const row = this.closest('tr');
            
            const deleteData = {
                // Don't send username from data attributes for security
                category: row.getAttribute('data-category'),
                expense_name: row.getAttribute('data-expense_name'),
                amount: row.getAttribute('data-amount'),
                date: row.getAttribute('data-date'),
                expenseAddedDate: row.getAttribute('data-expenseAddedDate')
            };
            
            console.log('Sending delete data:', deleteData); // For debugging
            
            fetch('delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(deleteData)
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.text();
            })
            .then(response => {
                alert(response);
                if (response.includes('successfully')) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting expense: ' + error.message);
            });
        }
    });
});
