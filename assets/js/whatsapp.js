
// if (phoneNumber != undefined && phoneNumber != '' && whatsapp_permission_view != undefined && whatsapp_permission_view == '1') {
    var WebURL = "https://whatsapp.educationvibes.co.in";
    console.log("whatsaap_script");
    var temp_client = [];
    var count_message = 0
    var count_chat = 0
    var scroll_status = true;

    var login_tokken = "";


    var last_message_idd = "";

    function toggle() {
        document.body.classList.toggle("dark-mode");
    }
    async function convertToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result.split(',')[1]);
            reader.onerror = error => reject(error);
            reader.readAsDataURL(file);
        });
    }

    function whats_app_toggle() {
        $(".whatsapp_Chat").toggle();
        $(".float_whatsapp_icon").toggle();
    }

    async function getChats(phoneNumber, limit = 0) {
        try {
            var response = await fetch(`${WebURL}/get-chats/${phoneNumber}/${limit}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ` + login_tokken
                }
            });
            var data = await response.json();
            if (data.status === 1) {
                var chatsDiv = document.getElementById(`chats`);
                if (data.chats.length > 0) {
                    await set_chat(data.chats)
                    await set_notification(data.unreadMessages)

                }


            } else {
                console.error("Failed to get chats: ", data.message);
            }
        } catch (err) {
            console.error("Error getting chats:", err);
        }
    }

    async function set_communication(communication_data, sanitizedChatId, status = 0) {
        console.log(communication_data);

        if (typeof sanitizedChatId === 'string') {
            if (sanitizedChatId.includes("@")) {
                sanitizedChatId = sanitizedChatId.split("@")[0]; // Extract the part before the "@"
                if (sanitizedChatId.length > 10) {
                    sanitizedChatId = sanitizedChatId.slice(-10); // Take the last 10 characters if length > 10
                }
            } else if (sanitizedChatId.length > 10) {
                sanitizedChatId = parseInt(sanitizedChatId).slice(-10);
            } else {
                sanitizedChatId = sanitizedChatId;
            }
        } else {
            console.log(sanitizedChatId);
            sanitizedChatId = parseInt(sanitizedChatId).length > 10 ? parseInt(sanitizedChatId).slice(-10) : sanitizedChatId; // Fallback or error handling
        }

        console.log("sanitizedChatId: " + sanitizedChatId);

        return new Promise((resolve, reject) => {
            try {
                let index = 0;
                let prefix_id = "";

                communication_data.forEach(async (communication) => {
                    try {
                        if (communication && communication.body !== undefined && (communication.body !== "" || communication.hasMedia === true)) {
                            const formattedTimestamp = communication.timestamp ? formatTimestamp(communication.timestamp) : '';
                            const is_send_tick = communication.ack ? communication.ack : 0;
                            const message = communication.body ? await linkify(communication.body) : '';
                            const { type: Type, mimetype: mediaType, body: mediaBase64, caption: mediaCaption = '' } = communication._data;
                            const mediaData = communication.mediaData;
                            const isForwarded = communication.isForwarded;
                            const fileName = (mediaData && mediaData.filename) ? mediaData.filename : 'image';

                            const is_send_tick_html = is_send_tick === 1 ? '<i class="fas fa-check-double"></i>' :
                                is_send_tick === 3 ? '<i class="fas fa-check-double blue"></i>' :
                                    '<i class="fas fa-check"></i>';

                            let mediaHtml = '';
                            let download = '';

                            if (mediaData) {
                                download = `<i class="fa fa-arrow-circle-down message-size-font" onclick="downloadBase64File('${mediaData.mimetype}','${mediaData.data}','${fileName}')"></i>`;
                                if (Type === 'image') {
                                    mediaHtml = `<img src="data:${mediaType};base64,${mediaData.data}" alt="Image" class="media-preview"/> <p class="image-download">${download}</p>`;
                                } else if (Type === 'video') {
                                    mediaHtml = `<video controls><source src="data:${mediaType};base64,${mediaData.data}" type="video/mp4"></video>`;
                                } else if (Type === 'document') {
                                    const fileTypeIcons = {
                                        'application/pdf': 'fa-file-pdf',
                                        'application/msword': 'fa-file-word',
                                        'application/vnd.ms-excel': 'fa-file-excel',
                                        'application/vnd.ms-powerpoint': 'fa-file'
                                    };
                                    const fileTypeColors = {
                                        'application/pdf': 'red',
                                        'application/msword': 'blue',
                                        'application/vnd.ms-excel': 'green',
                                        'application/vnd.ms-powerpoint': 'grey'
                                    };
                                    const defaultIcon = 'fa-file';
                                    const defaultColor = 'grey';

                                    const iconClass = fileTypeIcons[mediaType] || defaultIcon;
                                    const iconColor = fileTypeColors[mediaType] || defaultColor;

                                    mediaHtml = `<i class="message-size-font fas fa-solid ${iconClass}" style="color:${iconColor};"></i>`;
                                }
                            }

                            const communication_chat = communication.fromMe ? `
                            <div class="text text-sent message-box-${communication.id.id}">
                                ${mediaHtml}
                                ${mediaData && mediaData.filename ? `&nbsp;<span>${mediaData.filename}&nbsp;${download}</span>` : ''}
                                ${mediaCaption ? `<p>${mediaCaption}</p>` : ''}
                                <p>${message}</p>
                                <div class="a1-row a1-end a1-half-spaced-items timestamp">
                                    <span>${formattedTimestamp}</span>
                                    ${is_send_tick_html}
                                </div>
                            </div>` : `
                            <div class="text text-received  message-box-${communication.id.id}">
                                ${mediaHtml}
                                ${mediaData && mediaData.filename ? `&nbsp;<span>${mediaData.filename}&nbsp;${download}</span>` : ''}
                                ${mediaCaption ? `<p>${mediaCaption}</p>` : ''}
                                <p>${message}</p>
                                ${isForwarded ? `<p>Forwarded Message</p>` : ''}
                                <span class="timestamp a1-row a1-end">${formattedTimestamp}</span>
                            </div>`;

                            if (status === 0) {
                                console.log("Adding to WhatsApp chat");

                                if (index === 0) {
                                    $(`#communication_${sanitizedChatId}`).attr("data-last_message_id", communication.id.id);
                                }
                                $(`#communication_${sanitizedChatId}`).find(`.message-box-${communication.id.id}`).remove();
                                $(`#communication_${sanitizedChatId}`).append(communication_chat);
                            } else {
                                console.log("Adding to regular chat");

                                if (index === 0) {
                                    $(`#message-${sanitizedChatId}`).attr("data-last_message_id", communication.id.id);
                                }
                                $(`#message-${sanitizedChatId}`).find(`.message-box-${communication.id.id}`).remove();
                                $(`#message-${sanitizedChatId}`).append(communication_chat);
                            }
                        }
                        index++;
                    } catch (error) {
                        console.error("Error processing communication:", error);
                    }
                });

                if ($("#message-" + sanitizedChatId).length > 0) {
                    const chatContainer = document.querySelector(`#message-${sanitizedChatId}`);
                    scrollBottom(chatContainer);
                }
                if ($(".chat-main").length > 0 && $(`#comm_${sanitizedChatId}`).length > 0) {
                    const chatContainer = document.querySelector(".chat-main");
                    scrollBottom(chatContainer);
                }

                resolve();
            } catch (error) {
                console.error("Error in set_communication:", error);
                reject(error); // Reject the outer promise in case of any error
            }
        });
    }

    async function set_new_message_notification(message) {
        console.log("setdata");
        var messageData = [];
        messageData.push(JSON.parse(message));
        console.log(messageData);
        await set_communication(messageData, messageData[0].id.remote, 1);
        // await set_communication(messageData, messageData[0].id.remote);
        // await getChats(phoneNumber)
-
    }

    // public/script.js
    function connectWebSocket() {
        const ws = new WebSocket('wss://whatsapp.educationvibes.co.in/ws?phone=' + phoneNumber);
        // const ws = new WebSocket('ws://your-websocket-server-domain-or-ip:9000');
        ws.onopen = () => {
            console.log('WebSocket connection established');
        };

        ws.onmessage = async (event) => {
            const message = event.data;
            await set_new_message_notification(message);
        };

        ws.onclose = () => {
            console.log('WebSocket connection closed');
            connectWebSocket();
            pollClientReady(phoneNumber);
        };
    }

    function scrollBottom(element) {
        if ($(element).length > 0) {
            element.scrollTop = element.scrollHeight;
        }
    }



    async function goTo(chatid = "") {
        if (chatid != "") {
            await handleCommunicationChat(chatid);
            document.querySelector(".main").classList.toggle("open-message");
        }
        else {
            $(".main").hide();
            $("aside.aside").show();

        }
    }

    var scroll_status = true;

    // async function handleScroll(type = "", id = "", contact = '') {
    //     const friendsPanel = document.getElementById(id);
    //     const remainingScroll = friendsPanel.scrollHeight - friendsPanel.scrollTop - friendsPanel.clientHeight;
    //     if (friendsPanel.scrollTop === 0) {
    //     }
    // }


   async function send_whatsapp_message(id) {
        console.log(id);
        var formData = new FormData(document.getElementById(id));
        var contact = $("#" + id).find("#contact").val();
        const mediaFiles = $('#mediaFiles')[0].files;
        const mediaFilesArray = [];

        for (let i = 0; i < mediaFiles.length; i++) {
            const file = mediaFiles[i];
            const base64 = await convertToBase64(file);
            var mediaFile = {
                name: file.name,
                size: file.size,
                type: file.type,
                base64: base64
            };
            mediaFilesArray.push(mediaFile);
        }

        formData.append('mediaFiles', JSON.stringify(mediaFilesArray));
        formData.append(csrfData.token_name, csrfData.hash);
        // console.log(contact);

        $.ajax({
            type: 'POST',
            url: WebURL + '/send-message',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': 'Bearer ' + login_tokken // Pass the JWT token in the Authorization header
            },
            success: function (response) {
                $("#" + id).find("#message").val("");
                $("#" + id).find("input[type='file']").val("");
                $("#" + id).find("#whatsapp_template").val("");
                // get_whatsapp_message(phoneNumber, contact)
            },
            error: function (xhr, status, error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
            }
        });
    }



    function get_whatsapp_message(phoneNumber, contact, lastmessage_id = "") {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await fetch(`${WebURL}/communication-chat/${phoneNumber}/${contact}/${lastmessage_id}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${login_tokken}`
                    }
                });

                const data = await response.json();
                // console.log(data);

                if (data.status === 1) {
                    if (data.messages.length > 0) {
                        if (lastmessage_id === "") {
                            $("#message-" + contact).html('');
                        }

                        const communication_data = data.messages;
                        await set_communication(communication_data, contact, 1);

                        const chatContainer = document.querySelector("#message-" + contact);
                        scrollBottom(chatContainer);
                        // console.log("scroll");
                    }
                }

                resolve(data); // Resolve the promise with the fetched data
            } catch (err) {
                console.error("Error initializing client:", err);
                reject(err); // Reject the promise with the error
            }
        });
    }


    function linkify(inputText) {
        return new Promise((resolve, reject) => {
            try {
                let replacedText;
                const replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
                replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');

                const replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
                replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');

                const replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
                replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

                resolve(replacedText);
            } catch (error) {
                reject(error);
            }
        });
    }

    async function initializeClient(phoneNumber) {

        try {
            const response = await fetch(WebURL + `/add-client/${phoneNumber}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ` + login_tokken
                }
            });
            const data = await response.json();

            const clientDiv = document.createElement("div");
            clientDiv.id = `client-${phoneNumber}`;
            if (data.qrCodeUrl != "") {
                $("#client-" + phoneNumber).remove();
                clientDiv.innerHTML = `
                    <img id="qr_scanner" src="${data.qrCodeUrl}" alt="QR Code" />
                `;
                $(".qr_scanner").remove();
                $(".whatsapp-qr-scanner").append(`<img class="qr_scanner" src="${data.qrCodeUrl}" alt="QR Code" />`);
            }
            if (data.login_tokken != "") {
                login_tokken = data.loginToken;
            }
            if (data.invalid) {
                initializeClient(phoneNumber);
            }

            if ($("#clients").length > 0) {
                document.getElementById("clients").appendChild(clientDiv);
            }

            if (data.status === 1) {
                // $(".float_whatsapp_icon").show();
                pollClientReady(phoneNumber, clientDiv);
                return;
            } else {
                console.error("Initialization failed: ", data.message);
            }
        } catch (error) {
            console.error("Error initializing client:", error);
        }

    }

    async function pollClientReady(phoneNumber, clientDiv) {
        const intervalId = setInterval(async () => {
            try {
                const response = await fetch(WebURL + `/is-client-ready/${phoneNumber}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ` + login_tokken
                    }
                });
                const { ready } = await response.json();
                if (ready === 1) {
                    $(".whatsapp-notification-icon-color").removeClass("text-danger")
                    $(".whatsapp-notification-icon-color").addClass("text-success")
                    clearInterval(intervalId);
                    $("#qr_scanner").hide();
                    if ($(".whatsapp-logout-button").length > 0) {
                        $(".qr_scanner").addClass("opacity");
                    }
                    else {
                        $(".whatsapp-qr-scanner").append(`<button class="btn btn-danger whatsapp-logout-button" onclick="logout_whatsapp()">Whatsapp Logout</button>`);
                        $(".qr_scanner").addClass("opacity");
                    }
                    getChats(phoneNumber);
                }
            } catch (error) {
                console.error("Error polling client readiness:", error);
                clearInterval(intervalId);
            }
        }, 3000);
    }

    $(document).ready(() => {
        if (phoneNumber != undefined) {
            initializeClient(phoneNumber)
        }
    });



    function formatTimestamp(timestamp) {
        const momentTimestamp = moment(timestamp * 1000);

        if (moment().diff(momentTimestamp, 'days') < 7) {
            return momentTimestamp.fromNow();
        } else {
            return momentTimestamp.calendar();
        }
    }




    async function set_notification(notification_data = "") {
        $(".whatsapp_notification").html('');
        let notification_count = 0;

        const notifications = notification_data.map(async (notification) => {
            try {
                if (notification && notification.phonenumber) {
                    let phonenumber = notification.phonenumber.slice(-10);
                    if (phonenumber && lead_notification[phonenumber]) {
                        const lastNotificationData = notification.data[notification.data.length - 1];
                        const notificationTitle = lastNotificationData.mediaData ? 'Media file' : await linkify(lastNotificationData.body);
                        const notificationTimestamp = lastNotificationData.timestamp ? formatTimestamp(lastNotificationData.timestamp) : '';
                        let notification_html = `<li class="relative notification-wrapper" data-notification-id="${phonenumber}">
                        <a href="javascript:void(0)" onclick="init_lead(${lead_notification[phonenumber].id}, true,'#tab_proposals_whatsapp_li')" class="notification-top notification-link"><span class="label icon-total-indicator bg-warning icon-notifications whatsapp-notification-icon-count" >${notification.message_count}</span>
                            <div class="notification-box">
                                <div class="media-body">
                                    <span class="notification-title">${notificationTitle} - ${lastNotificationData._data.notifyName}/${lead_notification[phonenumber].name}</span><br>
                                    <small class="text-muted">
                                        <span class="text-has-action" data-placement="right" data-toggle="tooltip" data-title="${notificationTimestamp}">
                                            ${notificationTimestamp}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>`;
                        $(".whatsapp_notification").append(notification_html);
                        notification_count++;
                    }
                }
            } catch (error) {
                console.error(`Error processing notification for ${notification.phonenumber}:`, error);
            }
        });

        await Promise.all(notifications);

        if (notification_count > 0) {
            $(".whatsapp-notification-icon").text(notification_count);
            // $(".whatsapp-notification-icon").removeClass('hide');
        } else {
            // $(".whatsapp-notification-icon").addClass('hide');
        }
    }


    async function set_chat(chats) {
        return new Promise(async (resolve, reject) => {
            try {
                const profilePromises = chats.map(async (chat) => {
                    if (chat != null) {
                        let chat_id = chat.id.split("@")[0]; // Extract the part before the "@"
                        if (chat_id.length > 10) {
                            chat_id = chat_id.slice(-10); // Take the last 10 characters if length > 10
                        }

                        let name = chat.name;
                        let profile = ''; // Placeholder for profile URL
                        let pinned_html = '';


                        if (chat.pinned) {
                            pinned_html = ` <div class="a1-row a1-center-items-h a1-center-items-v pin-wrap">
                                    <i class="fas fa-map-pin"></i>
                                </div>`;
                        }

                        let count_html = '';
                        if (chat.unreadCount > 0) {
                            count_html = ` <span class="a1-row a1-center-items-h a1-center-items-v notification">${chat.unreadCount}</span>`;
                        }

                        let lastMessage = chat.lastMessage;
                        let formattedTimestamp = lastMessage && lastMessage.t ? formatTimestamp(lastMessage.t) : '';
                        let ack = lastMessage && lastMessage.ack ? lastMessage.ack : '';
                        let l_message = lastMessage && lastMessage.body ? await linkify(lastMessage.body) : '';

                        let is_send_tick_html = '<i class="fas fa-check"></i>';

                        if (ack === 1) {
                            is_send_tick_html = '<i class="fas fa-check-double"></i>';
                        } else if (ack === 3) {
                            is_send_tick_html = '<i class="fas fa-check-double blue"></i>';
                        }

                        try {
                            let profile_data = await getUserProfile(phoneNumber, chat.id);
                            if (profile_data.profilePicUrl != "") {
                                profile = profile_data.profilePicUrl;
                                chat.profile = profile_data.profilePicUrl;
                            } else {
                                console.error(`Failed to fetch profile for chat ${chat.id}: ${profile_data.message}`);
                            }
                        } catch (error) {
                            console.error(`Error fetching profile for chat ${chat.id}:`, error);
                            // You can choose to continue without profile or handle this case as needed
                        }

                        temp_client[chat.id] = chat;
                        return ` <div id="${chat_id}"
                        class="a1-row a1-center-items-v a1-padding a1-justify-items a1-spaced-items border-b friend active" onclick="goTo('${chat.id}')">
                        <img src="${profile}" class="profile-pic side-friend-profile-pic" alt="${name}">
                        <div class="a1-column a1-long a1-elastic">
                            <div class="a1-row a1-long a1-elastic">
                                <span class="a1-long a1-elastic">${name}</span>
                                <span class="timestamp">${formattedTimestamp}</span>
                            </div>
                            <div class="a1-row a1-center-items-v a1-justify-items a1-long">
                                <span class="message-preview">
                                    ${is_send_tick_html}
                                    <span>${l_message}</span>
                                </span>

                                <div class="a1-row a1-center-items-v a1-half-spaced-items">
                                   ${pinned_html}
                                   ${count_html}
                                    <i class="fas fa-chevron-down icon-color"></i>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    }
                });

                // Await all profile promises
                const chatDivs = await Promise.all(profilePromises);

                // Remove existing chats and append new ones
                chats.forEach((chat) => {
                    let chat_id = chat.id.split("@")[0]; // Extract the part before the "@"
                    if (chat_id.length > 10) {
                        chat_id = chat_id.slice(-10); // Take the last 10 characters if length > 10
                    }

                    if ($("#" + chat_id).length > 0) {
                        $("#" + chat_id).remove();
                    }
                });

                // Append all chat divs to the DOM
                $(".friends-panel").append(chatDivs.join(''));

                // Show/hide relevant sections
                $("#chats").show();
                $("#clients").hide();
                setTimeout(() => {
                    scroll_status = true;
                }, 2000);

                resolve(); // Resolve the promise when all chats are processed and appended
            } catch (error) {
                console.error('Error setting chats:', error);
                reject(error); // Reject the promise if any error occurs
            }
        });
    }

    function getUserProfile(phoneNumber, chatId) {
        return new Promise((resolve, reject) => {
            fetch(WebURL + `/get-user-profile/${phoneNumber}/${chatId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ` + login_tokken
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.status === 1) {
                        const profile = {
                            name: result.profile.name,
                            profilePicUrl: result.profile.profilePicUrl,
                            isGroup: result.profile.isGroup
                        };
                        resolve(profile);
                    } else {
                        reject(new Error(result.message));
                    }
                })
                .catch(error => {
                    console.error('Error fetching profile:', error);
                    reject(error);
                });
        });
    }


    async function handleCommunicationChat(chat_id, lastmessage_id = '', status = 0, auto = 0) {
        try {
            const response = await fetch(`${WebURL}/communication-chat/${phoneNumber}/${chat_id}/${lastmessage_id}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${login_tokken}`
                }
            });

            const data = await response.json();
            if (data.status !== 1) {
                return; // Exit early if status is not 1
            }

            let clientInfo = temp_client[chat_id];
            let sanitizedChatId;
            console.log("sanitizedChatId =", chat_id);

            if (typeof chat_id === 'string') {
                if (chat_id.includes("@")) {
                    sanitizedChatId = chat_id.split("@")[0]; // Extract the part before the "@"
                    if (sanitizedChatId.length > 10) {
                        sanitizedChatId = sanitizedChatId.slice(-10); // Take the last 10 characters if length > 10
                    }

                } else if (chat_id.length > 10) {
                    sanitizedChatId = parseInt(chat_id).slice(-10);
                } else {
                    sanitizedChatId = chat_id;
                }
            } else {
                sanitizedChatId = parseInt(chat_id).slice(-10); // Fallback or error handling
            }

            console.log("sanitizedChatId =", sanitizedChatId);
            if (status === 1) {
                await set_communication(data.messages, sanitizedChatId, 1);

            } else {
                const communication_chat_set = `
                <div id="comm_${sanitizedChatId}" class="a1-row a1-center-items-v a1-justify-items a1-half-padding-tb a1-padding-lr bg-left-panel-header a1-spaced-items">
                    <div class="a1-row a1-center-items-v a1-spaced-items">
                        <i class="fas fa-chevron-left blue back-arrow" onclick="goTo()"></i>
                        <img src="${clientInfo.profile}" class="profile-pic" alt="${clientInfo.name}">
                        <span>${clientInfo.name}</span>
                    </div>
                    <div class="a1-row a1-spaced-items a1-center-items-v icon-color">
                        <i class="fas fa-search"></i>
                        <i class="fas fa-paperclip"></i>
                        <i class="fas fa-ellipsis-v"></i>
                    </div>
                </div>
                <div class="a1-column a1-long a1-elastic">
                    <div  class="chat-container a1-column a1-long a1-elastic chat-main a1-spaced-items">
                    </div>
                    <div class="a1-row a1-spaced-items a1-center-items-v a1-padding bg-left-panel-header">
                        <i class="far fa-smile icon-color fa-1half"></i>
                        <input type="text" class="a1-long chat-input" placeholder="Type a message">
                        <i class="fas fa-microphone icon-color fa-1half"></i>
                    </div>
                </div>
            `;

                if (auto === 0) {
                    $("main.a1-column").html(communication_chat_set);
                    $("aside.aside").hide();
                    $(".main").show();
                }

                await set_communication(data.messages, sanitizedChatId);


            }

            if ($("#message-" + sanitizedChatId).length > 0) {
                const chatContainer = document.querySelector(`#message-${sanitizedChatId}`);
                scrollBottom(chatContainer);
            }
            if ($(".chat-main").length > 0 && $(`#comm_${sanitizedChatId}`).length > 0) {
                const chatContainer = document.querySelector(".chat-main");
                scrollBottom(chatContainer);
            }


        } catch (error) {
            console.error("Error handling communication chat:", error);
        }
    }


    function downloadBase64File(contentType, base64Data, fileName) {
        const linkSource = `data:${contentType};base64,${base64Data}`;
        const downloadLink = document.createElement("a");
        downloadLink.href = linkSource;
        downloadLink.download = fileName;
        downloadLink.click();
    }


    // connectWebSocket();

    

// }

