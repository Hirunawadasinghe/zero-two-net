<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '_inc/function.php';
    $meta_preset = [
        'title' => "AI Anime Chatbot",
        'description' => "Talk to cute AI anime girls and waifus anytime! Realistic, intelligent, and always available. Start chatting with your virtual girlfriend NOW!",
        'keywords' => [
            "AI chat girl",
            "anime chatbot",
            "waifu chat",
            "virtual girlfriend",
            "chat with AI waifu",
            "anime chat app",
            "chatbot anime girl",
            "talk to AI waifu",
            "virtual AI girlfriend online"
        ]
    ];
    include "layout/meta/def.php"; ?>
    <style>
        <?php echo minify('css/arcbot.css') ?>
    </style>
</head>

<body>
    <?php include 'layout/header/def.php' ?>
    <div class="chatbot-main-c">
        <div class="bot-background-c">
            <div class="bot-background-g"></div>
        </div>
        <div class="chatbot-main-c-div">
            <div class="chatbot-greating-msg">
                <h1>Hi! I'm Aqua ᓚᘏᗢ</h1>
                <span>Ask me anything related to anime—I got you!</span>
            </div>
            <div class="chatbot-m-w">
                <div class="chat-preview-w">
                    <div id="chat-preview"></div>
                </div>
                <div class="bot-input-w">
                    <div id="bot-input-suggestion-c"></div>
                    <form id="bot-input-form">
                        <textarea id="bot-prompt-textarea" placeholder="Message to ChatBot" autofocus></textarea>
                        <div class="bot-input-bottom-line">
                            <div class="bot-input-action-btn-c">
                                <div class="bot-input-action-btn" data-auto_fill="/search <anime name>">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="19px" viewBox="0 -960 960 960"
                                        width="19px" fill="#FFFFFF">
                                        <path
                                            d="M450-420q38 0 64-26t26-64q0-38-26-64t-64-26q-38 0-64 26t-26 64q0 38 26 64t64 26Zm193 160L538-365q-20 13-42.5 19t-45.5 6q-71 0-120.5-49.5T280-510q0-71 49.5-120.5T450-680q71 0 120.5 49.5T620-510q0 23-6.5 45.5T594-422l106 106-57 56ZM200-120q-33 0-56.5-23.5T120-200v-160h80v160h160v80H200Zm400 0v-80h160v-160h80v160q0 33-23.5 56.5T760-120H600ZM120-600v-160q0-33 23.5-56.5T200-840h160v80H200v160h-80Zm640 0v-160H600v-80h160q33 0 56.5 23.5T840-760v160h-80Z" />
                                    </svg>
                                    Search
                                </div>
                                <div class="bot-input-action-btn" data-auto_fill="/details <anime name>">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="19px" viewBox="0 -960 960 960"
                                        width="19px" fill="#FFFFFF">
                                        <path
                                            d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
                                    </svg>
                                    Details
                                </div>
                                <div class="bot-input-action-btn" type="menu" data-auto_fill="/random">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="19px" viewBox="0 -960 960 960"
                                        width="19px" fill="#FFFFFF">
                                        <path
                                            d="M560-160v-80h104L537-367l57-57 126 126v-102h80v240H560Zm-344 0-56-56 504-504H560v-80h240v240h-80v-104L216-160Zm151-377L160-744l56-56 207 207-56 56Z" />
                                    </svg>
                                    Random
                                </div>
                            </div>
                            <div id="chat-input-btn-c">
                                <button type="submit" class="send">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 -960 960 960"
                                        width="22px" fill="black">
                                        <path d="M440-160v-487L216-423l-56-57 320-320 320 320-56 57-224-224v487h-80Z" />
                                    </svg>
                                </button>
                                <button type="reset" class="stop">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="black">
                                        <path d="M320-640v320-320Zm-80 400v-480h480v480H240Zm80-80h320v-320H320v320Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        <?php
        echo minify('js/arcbot.js');
        echo minify('js/script.js');
        ?>
    </script>
</body>

</html>