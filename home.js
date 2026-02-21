document.querySelectorAll('.card').forEach(card =>{
      card.addEventListener('click', () => {
        document.getElementById('expenseModal').style.display = 'flex';
        let categoryName=card.querySelector("h3").innerText;
        document.getElementById("selectedCategory").value=categoryName;
      });
    });

    function closeModal(){
      document.getElementById('expenseModal').style.display = 'none';
    }

    function validateForm(){
      var amount=document.getElementById("amount").value;
      if(amount<=0){
        document.getElementById("aError").innerHTML='<br>Enter valid amount';
        return false;
      } 
      else{
        document.getElementById("aError").innerText="";
      }

      var date=document.getElementById("date").value;
      var input=new Date(date);
      var today=new Date();
      today.setHours(0,0,0,0);
      if(input>today){
        document.getElementById("dError").innerHTML='<br>Enter valid date';
        return false;
      } 
      else{
        document.getElementById("dError").innerText="";
      }
      return true;
    }
