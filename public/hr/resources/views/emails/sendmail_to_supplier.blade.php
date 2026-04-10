<!DOCTYPE html>
<html>

<head>
   <title>New Products Order from Amit Book Depot</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         background-color: #f9f9f9;
         margin: 0;
         padding: 0;
      }

      .container {
         max-width: 1000px;
         margin: 0 auto;
         background-color: #ffffff;
         border: 1px solid #e5e5e5;
         padding: 20px;
      }

      .header {
         background-color: #012738;
         padding: 10px 0;
         text-align: center;
      }

      .header img {
         max-width: 150px;
      }

      .content {
         padding: 20px 0px;
      }

      .content h2 {
         font-size: 24px;
         color: #000000;
         margin-bottom: 20px;
      }

      .content p {
         font-size: 16px;
         color: #444444;
         line-height: 1.5;
      }

      .button {
         display: block;
         width: 200px;
         margin: 20px auto;
         padding: 10px;
         background-color: #012738;
         color: #ffffff;
         text-align: center;
         text-decoration: none;
         border-radius: 5px;
      }

      .footer {
         background-color: #282828;
         color: #ffffff;
         text-align: center;
         padding: 10px 0;
         font-size: 14px;
      }

      .footer a {
         color: #ffffff;
         text-decoration: none;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 20px;
      }

      table,
      th,
      td {
         border: 1px solid #ddd;
      }

      th,
      td {
         padding: 10px;
         text-align: left;
      }

      th {
         background-color: #012738;
         color: white;
      }
   </style>
</head>

<body>
   <div class="container">
      <div class="header">
         <img src="https://www.amitbookdepot.com/public/uploads/logo/cJYFGlvjmufXdKGAFaVwvSg86NPKGoPlSl5u69eK.png"
            alt="Amit Book Depot">
      </div>
      <div class="content">
         <h2>New Products Order</h2>
         <table>
            <thead>
               <tr>
                  <th>Sr.no</th>
                  <th>Product Name</th>
                  <th>ISBN</th>
                  <th>Requested Quantity</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($products as $item)
                  <tr>
                     <td>{{ $item['productID'] }}</td>
                     <td>{{ $item['name'] }}</td>
                     <td>{{ $item['ISBN'] }}</td>
                     <td>{{ $item['newStock'] }}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
         <p>If you have any questions, feel free to contact us.</p>
      </div>
      <div class="footer">
         <p>Shop for <a href="#">Amit Book Depot</a></p>
         <p>Want to change how you receive these emails?<br>2024-25 Copy Right by Amit Book Depot</p>
      </div>
   </div>
</body>

</html>
