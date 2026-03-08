const bot_prompt_input = document.getElementById('bot-prompt-textarea');
const command_eliments = document.getElementsByClassName('bot-input-commands');

function refresh_bot_input_suggestions() {
    const input = bot_prompt_input.value.trim();
    if (input !== '') {
        const r = check_for_commands(input);
        if (r.length > 0) {
            document.getElementById('bot-input-suggestion-c').classList.add('expand');
            for (let i = 0; i < command_eliments.length; i++) {
                if (r.includes(command_eliments[i].dataset.command.split(' ')[0])) {
                    command_eliments[i].classList.add('show');
                } else {
                    command_eliments[i].classList.remove('show');
                }
            }
        } else {
            document.getElementById('bot-input-suggestion-c').classList.remove('expand');
        }
    } else {
        document.getElementById('bot-input-suggestion-c').classList.remove('expand');
    }
}

const bot_commands = [
    {
        "c": "/search",
        "des": "search for an anime",
        "fill": "<anime name>"
    },
    {
        "c": "/similar",
        "des": "find similar anime",
        "fill": "<anime name>"
    },
    {
        "c": "/details",
        "des": "get details about an anime",
        "fill": "<anime name>"
    },
    {
        "c": "/random",
        "des": "get a random anime"
    }
];

bot_commands.forEach(c => {
    document.getElementById('bot-input-suggestion-c').innerHTML += `<p class="bot-input-commands" data-command="${c.c + (c.fill ? ' ' + c.fill : '')}"><span>${c.c}</span> - ${c.des}</p>`;
});

function check_for_commands(t) {
    let r = [];
    t = t.toLowerCase();
    bot_commands.forEach(c => {
        c = c.c.toLowerCase();
        let sim = 0;
        for (let i = 0; i < t.length; i++) {
            if (c[i] === t[i]) {
                sim++;
            } else {
                break
            }
        }
        if (sim === t.length) { r.push(c) }
    });
    return r
}

for (let i = 0; i < command_eliments.length; i++) {
    command_eliments[i].onclick = () => {
        bot_prompt_input.value = command_eliments[i].dataset.command;
        refresh_bot_input_suggestions();
    }
}

bot_prompt_input.addEventListener('input', () => {
    refresh_bot_input_suggestions();
});

const bot_quick_action_btns = document.getElementsByClassName('bot-input-action-btn');
for (let i = 0; i < bot_quick_action_btns.length; i++) {
    bot_quick_action_btns[i].onclick = () => {
        bot_prompt_input.value = bot_quick_action_btns[i].dataset.auto_fill;
    }
}


const bot_input_form = document.getElementById("bot-input-form");
bot_input_form.addEventListener("submit", function (e) {
    send_prompt(e);
    refresh_bot_input_suggestions();
});
bot_input_form.addEventListener("keypress", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
        send_prompt(e);
        refresh_bot_input_suggestions();
    }
});
bot_input_form.addEventListener("reset", () => {
    allow_message_sending(true);
});


let is_respond_pending = false;
let chatbot_array = [];
const max_prompt_char_length = 300;
let token_c = JSON.parse(localStorage.getItem('chatBot_token')) || { state: 'p', data: 0 };

async function send_prompt(e) {
    e.preventDefault();
    const input = bot_prompt_input.value.trim();
    if (is_respond_pending || input === '') { return }
    bot_prompt_input.value = '';

    if (input.length > 300) {
        send_to_chat(input, 'user', false);
        send_to_chat(pick_ran_error('too_long'), 'assistant', false);
        return
    }

    if (token_c['state'] === 's') {
        if (Date.now() > token_c['data']) {
            token_c = { state: 'p', data: 0 };
            localStorage.setItem('chatBot_token', JSON.stringify(token_c));
        } else {
            send_to_chat(input, 'user', false);
            const time_def = (token_c['data'] - Date.now()) / 1000;
            send_to_chat(`The limit has been reached. Please wait ${time_def < 60 ? Math.floor(time_def) + ' seconds' : Math.floor(time_def / 60) + ' minutes'} and try again.`, 'assistant', false);
            return
        }
    }

    let deliver_arr;
    if (input[0] === '/') {
        send_to_chat(input, 'user', false);
        const msg_command = input.split(' ', 2)[0].trim();
        const msg_content = input.slice(msg_command.length).trim();
        deliver_arr = [{ 'command': msg_command, "content": msg_content }];
    } else {
        send_to_chat(input, 'user', true);
        deliver_arr = chatbot_array;
    }

    try {
        fetch('api/arcbot.php?t=' + encodeURIComponent(JSON.stringify(deliver_arr))).then(r => r.json()).then(d => {
            if (d?.msg_type) {
                send_to_chat(d.msg, 'assistant', false);
            } else if (d?.msg) {
                token_c['data']++;
                if (token_c['data'] < 30) {
                    localStorage.setItem('chatBot_token', JSON.stringify({ state: 'p', data: token_c['data'] }));
                } else {
                    token_c = { state: 's', data: Date.now() + (5 * 60 * 1000) };
                    localStorage.setItem('chatBot_token', JSON.stringify(token_c));
                }
                send_to_chat(d.msg, 'assistant', true);
            } else {
                console.error('Error from the bot:', d.error);
                send_to_chat(pick_ran_error('unknown'), 'assistant');
            }
        })
    } catch (err) {
        console.error('Error getting data from bot:', err);
        send_to_chat(pick_ran_error('unknown'), 'assistant');
    }
}


const chat_preview_w = document.getElementsByClassName('chat-preview-w')[0];

function send_to_chat(text, from, add_to_array = false) {
    if (chatbot_array.length === 0) {
        document.getElementsByClassName('chatbot-greating-msg')[0].classList.add('collaps');
        document.getElementsByClassName('chatbot-m-w')[0].classList.add('expand');
        document.getElementsByClassName('chatbot-main-c-div')[0].classList.add('expand');
        document.getElementsByClassName('bot-background-c')[0].classList.add('blur');
        document.getElementsByClassName('bot-background-g')[0].classList.add('expand');
    }

    if (add_to_array) {
        chatbot_array.push({ "role": from, "content": text });
        if (chatbot_array.length > 10) { chatbot_array = chatbot_array.slice(1) }
    }

    const main_m_c = document.createElement('div');
    main_m_c.classList.add('chatbot-message');
    main_m_c.classList.add(from === 'user' ? 'msg-from-user' : 'msg-from-bot');
    document.getElementById('chat-preview').appendChild(main_m_c);

    const e = document.createElement('div');
    e.classList.add('chatbot-message-cw');
    main_m_c.appendChild(e);

    allow_message_sending(false);

    function scroll_bottom() {
        chat_preview_w.scrollTo({ top: chat_preview_w.scrollHeight, behavior: "smooth" });
    }

    if (from === 'assistant') {
        let i = 0;
        let t_msg = '';
        let is_html_tag_open = false;
        function print() {
            if (i < text.length && is_respond_pending) {
                if (text[i + 1] === '<') {
                    is_html_tag_open = true;
                } else if (text[i] === '>') {
                    is_html_tag_open = false;
                }
                t_msg += text[i];
                // Encode & Decode HTML entities
                let tmc = document.createElement("textarea");
                tmc.innerHTML = t_msg;
                e.innerHTML = tmc.value;
                if (chat_preview_w.scrollTop + chat_preview_w.clientHeight >= chat_preview_w.scrollHeight - 75) { scroll_bottom(); }
                i++;
                (!is_html_tag_open && text[i] !== ' ') ? setTimeout(print, (Math.random() * 12) + 15) : print();
            } else {
                allow_message_sending(true);
            }
        }
        print();
    } else {
        e.innerHTML = text;
        allow_message_sending(true);
    }
    scroll_bottom();
}


const chat_inpu_btn_c = document.getElementById('chat-input-btn-c');
function allow_message_sending(a) {
    if (a) {
        chat_inpu_btn_c.classList.remove('block-chatbot-submit');
        is_respond_pending = false;
    } else {
        chat_inpu_btn_c.classList.add('block-chatbot-submit');
        is_respond_pending = true;
    }
}


const bot_error_msgs = {
    too_long: [
        "Your text is too long. Make it shorter 😖",
        "Hey, hey, hey! That’s way too long! Trim it down 😤",
        "Ugh! My brain can't take this! Cut it down 😵"
    ],
    unknown: [
        "Oops! Something broke. Try again! 🔧",
        "Error! Error! Try once more! 🚨🔄",
        "Whoops! Something went wrong. Try again 😣"
    ]
};
function pick_ran_error(t) {
    if (bot_error_msgs[t]) {
        return bot_error_msgs[t][Math.floor(Math.random() * bot_error_msgs[t].length)]
    } else {
        return bot_error_msgs.unknown[Math.floor(Math.random() * bot_error_msgs.unknown.length)]
    }
}