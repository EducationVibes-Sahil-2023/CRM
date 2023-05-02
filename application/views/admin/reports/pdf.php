<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
    .leadSum .panel_s .panel-body {
        min-height: 400px;
    }
    .leadSum {
        padding: 10px;

    }
    * {
        font-size: 14px !important;
    }

    .panel_s .panel-body {
        margin: 0px !important;
        padding: 0px !important;
    }

    h3.bold {
        margin: 0px !important;
        padding: 0px !important;
    }

    .panel-body>h4 {
        padding-top: 10px !important;
        padding-left: 10px !important;
    }

    .border-right {
        padding: 20px !important;
        line-height: 24px !important;
    }

</style>

<div style="display:none1;">
    <div class="leadSum" id="pdf_generate">
        <?= $html_content ?>
    </div>

</div>

<script>
    const button = document.getElementById('download-button');
    var myWindow = window.open("", "_self"); // Open a new window
    myWindow.opener = null; // Detach the new window from the current window

    async function generatePDF() {
        // Choose the element that your content will be rendered to.
        // const element = document.getElementById('pdf_generate');
        // // Choose the element and save the PDF for your user.
        // html2pdf().from(element).save();
        const element = document.getElementById('pdf_generate');
        const options = {
            filename: 'Report.pdf',
            margin: [10, 0, 10, 0],
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                dpi: 192,
                scale: 2,
                letterRendering: true,
                useCORS: true,
            },
            pagebreak: {
                mode: 'avoid-all',
                after: '.break-page'
            },
            jsPDF: {
                unit: 'mm',
                format: 'a3',
                orientation: 'landscape'
            }
        };
        await html2pdf().set(options).from(element).save();
        // await html2pdf().from(element).set(options).toPdf().output('datauristring').then(function(res) {
        //     console.log(res);
        // });
        // this.blobString = res;
        // await html2pdf().set(options).from(element).save();
        // console.log("sjkcbns");
        // var objWindow = window.open(location.href, "_self");
        // objWindow.close();
    }
    setTimeout(() => {
        generatePDF();
    }, 1000);
    // button.addEventListener('click', generatePDF);
</script>