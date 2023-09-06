<style>
    /* Add your custom CSS here */
    body {
        background-color: #f8f9fa;
    }

    .form-container {
        background-color: #ffffff;
        border: 1px solid #ccc;
        padding: 20px;
        margin-top: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-container .mt-5 {
        margin-top: 10px;
    }

    .form-container .datepicker {
        width: 100%;
    }

    .form-container .form-control {
        width: 100%;
    }

    .form-container .selectpicker {
        width: 100%;
    }
</style>
<h2 class="text-center">Accommodation & Flight</h2>
<div class="row">
    <div class="col-md-12">
        <div class="form-container">
            <label for="vendor-select">Select Vendor</label>
            <select id="vendor-select" class="selectpicker form-control">
                <option>Select Vendor</option>
                <option>Vendor 1</option>
                <option>Vendor 2</option>
                <option>Vendor 3</option>
            </select>

            <div class="mt-5">
                <label for="address">Place & Address</label>
                <textarea id="address" placeholder="Address" class="form-control"></textarea>
            </div>

            <div class="row mt-5">
                <div class="col-md-4">
                    <label for="contract-file">Contract</label>
                    <input type="file" id="contract-file" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="start-date">Select Start Date</label>
                    <input type="text" id="start-date" placeholder="Select Start Date" class="datepicker form-control">
                </div>
                <div class="col-md-4">
                    <label for="end-date">Select End Date</label>
                    <input type="text" id="end-date" placeholder="Select End Date" class="datepicker form-control">
                </div>
            </div>

            <div class="mt-5">
                <label for="payment-receipt">Payment (Receipt)</label>
                <input type="file" id="payment-receipt" class="form-control">
            </div>

            <h4 class="fs-title mt-5">Contact Person Details</h4>
            <hr>

            <div class="row mt-5">
                <div class="col-md-4">
                    <label for="contact-name">Contact Name</label>
                    <input type="text" id="contact-name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="contact-email">Contact Email</label>
                    <input type="email" id="contact-email" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="contact-phone">Contact Phone</label>
                    <input type="number" id="contact-phone" class="form-control">
                </div>
            </div>

            <h4 class="fs-title mt-5">Flight Details</h4>
            <hr>

            <div class="row mt-5">
                <div class="col-md-4">
                    <label for="flight-name">Flight Name</label>
                    <input type="text" id="flight-name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="flight-date-time">Flight Date & Time</label>
                    <input type="text" id="flight-date-time" class="datepicker form-control">
                </div>
                <div class="col-md-4">
                    <label for="flight-ticket">Flight Ticket</label>
                    <input type="file" id="flight-ticket" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>