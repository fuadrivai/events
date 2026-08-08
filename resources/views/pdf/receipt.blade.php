<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Receipt</title>
  </head>
  <body
    style="
      margin: 0;
      padding: 20px;
      font-family: &quot;Roboto&quot;, &quot;Poppins&quot;, Arial, sans-serif;
      background-color: #f8f9fa;
      color: #333;
    "
  >
    <div
      style="
        max-width: 700px;
        margin: 0 auto;
        background: white;
        overflow: hidden;
      "
    >
      <!-- Header -->
      <div style="padding: 25px 30px; border-bottom: 1px solid #eaeaea">
        <div
          style="
            display: flex;
            justify-content: space-between;
            align-items: center;
          "
        >
          <div>
            @if(!empty($image_data_uri))
              <img src="{{ $image_data_uri }}" style="height:40px;">
            @endif

          </div>
          <div style="text-align: right; color: #666; font-size: 14px">
            <strong style="color: #007bff">Payment Date:</strong><br />
            {{ $transaction_timestamp }}
          </div>
        </div>
      </div>

      <!-- Body Section 1: Invoice Info -->
      <div style="padding: 30px; text-align: center">
        <h1
          style="
            margin: 0 0 25px 0;
            color: #007bff;
            font-size: 28px;
            font-weight: 600;
          "
        >
          Payment Receipt
        </h1>

        <div
          style="
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-bottom: 20px;
            flex-wrap: wrap;
          "
        >
          <div style="text-align: left">
            <div style="margin-bottom: 8px">
              <strong style="color: #007bff">Invoice Number:</strong><br />
              {{ $external_id }}
            </div>
            <div style="margin-bottom: 8px">
              <strong style="color: #007bff">Date:</strong><br />
              {{ $dateResv }}
            </div>
          </div>
          <div style="text-align: left">
            <div style="margin-bottom: 8px">
              <strong style="color: #007bff">Status Payment:</strong><br />
              <strong style="font-weight: bold; color: green">PAID</strong>
            </div>
            <div style="margin-bottom: 8px">
              <strong style="color: #007bff">Time:</strong><br />
              {{ $timeResv }}
            </div>
          </div>
        </div>
      </div>

      <!-- Body Section 2: Payment Detail -->
      <div style="padding: 0 30px 30px 30px">
        <table style="width: 100%; border-collapse: collapse">
          <thead>
            <tr style="background-color: #f8f9fa">
              <th
                style="
                  text-align: left;
                  padding: 15px;
                  border-bottom: 1px solid #eaeaea;
                  color: #007bff;
                  font-weight: 600;
                "
              >
                Description
              </th>
              <th
                style="
                  text-align: right;
                  padding: 15px;
                  border-bottom: 1px solid #eaeaea;
                  color: #007bff;
                  font-weight: 600;
                "
              >
                Amount
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding: 15px; border-bottom: 1px solid #eaeaea">
                {{ $description }}
              </td>
              <td
                style="
                  padding: 15px;
                  border-bottom: 1px solid #eaeaea;
                  text-align: right;
                "
              >
                Rp{{ $fee }}
              </td>
            </tr>
            <tr>
              <td style="padding: 15px; border-bottom: 1px solid #eaeaea">
                Bank Charger
              </td>
              <td
                style="
                  padding: 15px;
                  border-bottom: 1px solid #eaeaea;
                  text-align: right;
                "
              >
                Rp 3.663
              </td>
            </tr>
            <tr style="background-color: #f8f9fa">
              <td style="padding: 15px; font-weight: 600; color: #007bff">
                Total Payment
              </td>
              <td
                style="
                  padding: 15px;
                  text-align: right;
                  font-weight: 600;
                  color: #007bff;
                "
              >
                Rp{{ $total }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div
        style="
          padding: 25px 30px;
          background-color: #f8f9fa;
          border-top: 1px solid #eaeaea;
          text-align: center;
        "
      >
        <p style="margin: 0 0 15px 0; color: #666; font-style: italic">
          "Thank you for your payment. Please keep this receipt for your
          record."
        </p>
        <p style="margin: 0; color: #007bff; font-weight: 500">
          Mutiara Harapan Islamic School
        </p>
      </div>
    </div>
  </body>
</html>
