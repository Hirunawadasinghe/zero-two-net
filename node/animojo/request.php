<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/_config.php';
    $meta_preset = ['title' => "Request Form - $site_name"];
    include "layout/meta/def.php" ?>
    <style>
        .input-form {
            margin: 3% auto;
            max-width: 23em;
            display: flex;
            flex-direction: column;
            background-color: transparent;
            gap: 1.5em;
        }

        .input-form div {
            display: flex;
            flex-direction: column;
        }

        .input-form div label {
            margin: 0 0 .5em 0;
            color: white;
        }

        .input-form div label span {
            color: crimson;
        }

        .input-form div h1 {
            text-align: center;
            margin: 0 0 .7em 0;
            font-weight: 600;
            color: white;
            font-size: 22px;
        }

        .input-form div p {
            font-size: .95rem;
            margin: 0 0 .5em 0;
            text-align: justify;
            color: white;
        }

        .input-form div input,
        .input-form div textarea {
            background-color: var(--dark-blue-color);
            border: 1px solid var(--light-blue-color);
            font-size: .9em;
            border-radius: 6px;
            padding: .7em;
            outline: none;
            color: white;
        }

        .input-form div textarea {
            min-height: 10em;
            padding: .5em;
            resize: vertical;
        }

        .input-form div div {
            margin: .5em 0 0 0;
            display: none;
            flex-direction: row;
            align-items: baseline;
            color: crimson;
            gap: .45rem;
        }

        .input-form div div span {
            font-size: .9em;
            color: crimson;
            font-weight: normal;
            margin: 0;
        }

        .input-form button {
            background-color: var(--light-blue-color);
            padding: .8em 0;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-size: .95rem;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php include "layout/header/def.php" ?>
    <div class="content-wraper">
        <div class="content">
            <form class="input-form">
                <div>
                    <h1>Request Form</h1>
                    <p>If you have any anime requests, feel free to let us know here.</p>
                    <p>ඔයාලට ඕන අලුත් ඇනිමෙ එකක් හෝ සින්හල සබ් ඕන ඇනිමෙ තියෙනව නම් මෙතනින් අපිට කියන්න​.</p>
                </div>
                <div>
                    <label for="Name">Name <span>*</span></label>
                    <input type="text" id="Name" placeholder="Your Name" class="inputs required">
                    <div id="Name-alert"><i class="fa-solid fa-circle-info"></i>
                        <span>This is a required question</span>
                    </div>
                </div>
                <div>
                    <label for="Contact">Contact Method</label>
                    <input type="text" id="Contact" placeholder="e.g. Telegram: @username" class="inputs">
                </div>
                <div>
                    <label for="Message">Message <span>*</span></label>
                    <textarea type="text" id="Message" placeholder="e.g. Naruto, Attack on Titan, Darling in the Franxx"
                        class="inputs required"></textarea>
                    <div id="Message-alert"><i class="fa-solid fa-circle-info"></i>
                        <span>This is a required question</span>
                    </div>
                </div>
                <button type="button" id="button" onclick="submit_request()" class="center">Request</button>
            </form>
            <div class="r-panel"><?php include 'layout/sidebar/new.php' ?></div>
        </div>
    </div>
    <script>
        <?php echo minify('js/request.js') ?>
    </script>
    <?php include "layout/footer/def.php" ?>
</body>

</html>