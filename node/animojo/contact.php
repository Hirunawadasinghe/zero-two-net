<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/_config.php';
    $meta_preset = ['title' => "Contact Form - $site_name"];
    include "layout/meta/def.php";
    ?>
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
            font-size: 22px;
            color: white;
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
                    <h1>Contact Form</h1>
                    <p>Please submit your inquiry using the form below, we’ll reach out to you as soon as
                        possible.</p>
                </div>
                <div>
                    <label for="Name">Name <span>*</span></label>
                    <input type="text" id="Name" placeholder="Your Name" class="inputs required">
                    <div id="Name-alert"><i class="fa-solid fa-circle-info"></i>
                        <span>This is a required question</span>
                    </div>
                </div>
                <div>
                    <label for="Contact">Contact Method <span>*</span></label>
                    <input type="text" id="Contact" placeholder="e.g. Telegram: @username" class="inputs required">
                    <div id="Contact-alert"><i class="fa-solid fa-circle-info"></i>
                        <span>This is a required question</span>
                    </div>
                </div>
                <div>
                    <label for="Message">Message <span>*</span></label>
                    <textarea type="text" id="Message" placeholder="Your message..." class="inputs required"></textarea>
                    <div id="Message-alert"><i class="fa-solid fa-circle-info"></i>
                        <span>This is a required question</span>
                    </div>
                </div>
                <button type="button" id="button" onclick="submit_request()" class="center">Send</button>
            </form>
            <div class="r-panel"><?php include 'layout/sidebar/new.php' ?></div>
        </div>
    </div>
    <script>
        <?php echo minify('js/contact.js') ?>
    </script>
    <?php include "layout/footer/def.php" ?>
</body>

</html>