
var WebURL = "http://localhost:9000";

var temp_client = [];
var count_message = 0
var count_chat = 0
var scroll_status = true;

var login_tokken = "";
// document.getElementById("phoneNumberForm").addEventListener("submit", async (event) => {
//     event.preventDefault();
//     phoneNumber = document.getElementById("phoneNumberInput").value;
//     await initializeClient(phoneNumber);
//     document.getElementById("phoneNumberInput").value = ""; // Clear the input
// });

// async function initializeClient(phoneNumber) {
//     try {
//         var response = await fetch(WebURL + `/add-client/${phoneNumber}`);
//         var data = await response.json();

//         var clientDiv = document.createElement("div");
//         clientDiv.id = `client-${phoneNumber}`;
//         clientDiv.innerHTML = `
//     <h2>Client for ${phoneNumber}</h2>
//     <p>${data.message}</p>
//     <img id="qr_scanner" src="${data.qrCodeUrl}" alt="QR Code" />
//   `;
//         document.getElementById("clients").appendChild(clientDiv);

//         if (data.status === 1) {
//             pollClientReady(phoneNumber, clientDiv);
//         } else {
//             console.error("Initialization failed: ", data.message);
//         }
//     } catch (err) {
//         console.error("Error initializing client:", err);
//     }
// }


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
        }
        if (data.login_tokken != "") {
            login_tokken = data.loginToken;
        }
        if (data.invalid) {
            initializeClient(phoneNumber);
        }

        document.getElementById("clients").appendChild(clientDiv);

        if (data.status === 1) {
            $(".float_whatsapp_icon").show();
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
                clearInterval(intervalId);
                $("#qr_scanner").hide();
                getChats(phoneNumber);
            }
        } catch (error) {
            console.error("Error polling client readiness:", error);
            clearInterval(intervalId);
        }
    }, 3000);
}

$(document).ready(() => {
    // pollClientReady(phoneNumber);
    initializeClient(phoneNumber);
});



function formatTimestamp(timestamp) {
    const momentTimestamp = moment(timestamp * 1000);

    if (moment().diff(momentTimestamp, 'days') < 7) {
        return momentTimestamp.fromNow();
    } else {
        return momentTimestamp.calendar();
    }
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
        console.log(data);

        if (data.status === 1) {
            var chatsDiv = document.getElementById(`chats`);
            if (data.chats.length > 0) {
                await set_chat(data.chats)
            }

        } else {
            console.error("Failed to get chats: ", data.message);
        }
    } catch (err) {
        console.error("Error getting chats:", err);
    }
}

async function set_chat(chats) {
    return new Promise(async (resolve, reject) => {
        try {
            const profilePromises = chats.map(async (chat) => {
                if (chat != null) {
                    let chat_id = chat.id.split("@")[0];
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
                    let l_message = lastMessage && lastMessage.body ? lastMessage.body : '';

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
                let chat_id = chat.id.split("@")[0];
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



async function handleCommunicationChat(chat_id, lastmessage_id = '') {
    try {
        var response = await fetch(`${WebURL}/communication-chat/${phoneNumber}/${chat_id}/${lastmessage_id}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ` + login_tokken
            }
        });
        var data = await response.json();
        if (data.status == 1) {
            let clientInfo = temp_client[chat_id];
            let sanitizedChatId = clientInfo.id.split("@")[0];

            let communication_chat_set = `
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
    <div onscroll="handleScroll(2,'communication_${sanitizedChatId}')" id="communication_${sanitizedChatId}" class="chat-container a1-column a1-long a1-elastic chat-main a1-spaced-items">
    </div>
    <div  class="a1-row a1-spaced-items a1-center-items-v a1-padding bg-left-panel-header">
      <i class="far fa-smile icon-color fa-1half"></i>
      <input type="text" class="a1-long chat-input" placeholder="Type a message">
      <i class="fas fa-microphone icon-color fa-1half"></i>
    </div>
  </div>
`;

            $("main.a1-column").html(communication_chat_set);

            await set_communication(data.messages, sanitizedChatId)

            $("aside.aside").hide();
            $(".main").show();

            var chatContainer = document.querySelector(".chat-main");
            scrollBottom(chatContainer);
        }

    } catch (error) {
        console.error("Error handling communication chat:", error);
    }
}


function set_communication(communication_data, sanitizedChatId) {
    return new Promise((resolve, reject) => {
        try {
            communication_data.forEach((communication) => {
                console.log(communication);
                if (communication && communication.body != "") {
                    let formattedTimestamp = communication && communication.timestamp ? formatTimestamp(communication.timestamp) : ''; // Assuming timestamp is in seconds
                    let is_send_tick = communication && communication.ack ? communication.ack : '';
                    let message = communication && communication.body ? communication.body : '';
                    let is_send_tick_html = `<i class="fas fa-check"></i>`;
                    if (is_send_tick === 1) {
                        is_send_tick_html = `<i class="fas fa-check-double "></i>`;
                    } else if (is_send_tick === 3) {
                        is_send_tick_html = `<i class="fas fa-check-double blue"></i>`;
                    }

                    let communication_chat = "";
                    if (communication.fromMe) {
                        communication_chat = `
                <div class="text text-sent">
                  <p>${message}</p>
                  <div class="a1-row a1-end a1-half-spaced-items timestamp">
                    <span>${formattedTimestamp}</span>
                    ${is_send_tick_html}
                  </div>
                </div>`;
                    } else {
                        communication_chat = `
                <div class="text text-received">
                  <p>${message}</p>
                  <span class="timestamp a1-row a1-end">${formattedTimestamp}</span>
                </div>`;
                    }

                    $(`#communication_${sanitizedChatId}`).append(communication_chat);
                }
            });

            setTimeout(() => {
                scroll_status = true;
            }, 2000);
            resolve(); // Resolve the promise when the operation completes successfully
        } catch (error) {
            reject(error); // Reject the promise if an error occurs
        }
    });
}
function scrollBottom(element) {
    element.scrollTop = element.scrollHeight;
}

function toggle() {
    document.body.classList.toggle("dark-mode");
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


async function handleScroll(type, id) {
    const friendsPanel = document.getElementById(id);
    // Calculate remaining scroll distance
    console.log("start");
    const remainingScroll = friendsPanel.scrollHeight - friendsPanel.scrollTop - friendsPanel.clientHeight;

    if (remainingScroll <= 0 && scroll_status === true) {
        if (type == 1) {
            scroll_status = false;
            await getChats(phoneNumber, $(".friends-panel .friend").length);

        }
        else if (type == 2) {
            scroll_status = false;
            await handleCommunicationChat(chat_id, lastmessage_id = '')

        }

    }
}


function send_whatsapp_message(id="") {

    console.log(id);
    // Prepare data object with phone number, contact, and message
    var sendInfo = {
        phoneNumber: $("#phoneNumber").val(),
        contact: $("#contact").val(),
        message: $("#message").val()
    };

    // // Append media files to sendInfo if they exist
    const mediaFiles = document.getElementById('mediaFiles').files;
    if (mediaFiles.length > 0) {
        sendInfo.mediaFiles = Array.from(mediaFiles); // Convert FileList to Array
    }

    // // Split and trim media URLs and add to sendInfo if they exist

    const mediaUrlsValue = [];
    if (mediaUrlsValue.length>0) {
        const mediaUrls = mediaUrlsValue.split(',').map(url => url.trim());
        sendInfo.mediaPaths = mediaUrls;
    }

    // // Log the sendInfo object for debugging purposes
    console.log(sendInfo);

    // Send the AJAX request
    $.ajax({
        type: "POST",
        url: WebURL + '/send-message',
        data: JSON.stringify(sendInfo), // Convert sendInfo object to JSON string
        contentType: "application/json", // Set content type to JSON
        headers: {
            'Authorization': 'Bearer ' + login_tokken // Pass the JWT token in the Authorization header
        },
        success: function (response) {
            if (response.status === 1) {
                alert("Message sent successfully!");
                // location.reload(true); // Reload the page if needed
            } else {
                alert("Failed to send message: " + response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error sending message:', error);
            alert("Failed to send message. Please try again.");
        }
    });
}



function whats_app_toggle() {
    $(".whatsapp_Chat").toggle();
    $(".float_whatsapp_icon").toggle();
}