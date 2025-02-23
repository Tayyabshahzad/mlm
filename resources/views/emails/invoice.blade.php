<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Global Visioners International</title>

		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
        margin-top: 100px;;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box ">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img
										src="{{ asset('assets/custom-images/gvi-no-background.png') }}"
										style="width: 100%; max-width: 200px"
									/>
								</td> 
								<td>
									Invoice #: 123<br />
									Created: {{ $user->updated_at }}  <br /> 
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									Sparksuite, Inc.<br />
									12345 Sunny Road<br />
									Sunnyville, CA 12345
								</td>

								<td>
								 
									{{  $user->name }}<br />
									{{  $user->email }}
								</td>
							</tr>
						</table>
					</td>
				</tr>

			 

				 

				<tr class="heading">
					<td>Detail</td>

					<td>Price(PKR)</td>
				</tr>

				<tr class="item">
					<td>Invested Amount</td>

					<td>25,000.00</td>
				</tr>

				<tr class="item">
					<td>Registration Fee</td>

					<td>2500.00</td>
				</tr>

				 

				<tr class="total">
					<td></td>

					<td>Total: 27,500.00</td>
				</tr>
			</table>

      <p>
        <b>Note:</b>
        This receipt is computer generated and doesn’t need a signature or stamp.
      </p>
		</div>
	</body>
</html>