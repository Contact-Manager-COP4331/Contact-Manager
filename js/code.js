const urlBase = 'http://group19cop4331.xyz/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function switchDiv(id1,id2)
{
	const div1 = document.getElementById(id1);
	const div2 = document.getElementById(id2);

	div1.classList.toggle("d-none");
	div2.classList.toggle("d-none");
}

function showSection(sectionId)
{
	const sections = ['readSection','addSection','deleteSection','editSection'];
	for (const id of sections)
	{
		const el = document.getElementById(id);
		if (!el)
		{
			continue;
		}
		if (id === sectionId)
		{
			el.classList.remove('d-none');
		}
		else
		{
			el.classList.add('d-none');
		}
	}
}

document.addEventListener('DOMContentLoaded', function()
{
	initializeRequiredFieldIndicators();
});

function initializeRequiredFieldIndicators()
{
	const fields = document.querySelectorAll('.required-field input, .required-field textarea, .required-field select');
	fields.forEach(function(field)
	{
		const wrapper = field.closest('.required-field');
		if (!wrapper)
		{
			return;
		}

		const updateState = function()
		{
			if (field.value && field.value.trim().length > 0)
			{
				wrapper.classList.add('has-value');
			}
			else
			{
				wrapper.classList.remove('has-value');
			}
		};

		field.addEventListener('input', updateState);
		field.addEventListener('change', updateState);
		updateState();
	});
}
function doRegister()
{
	let firstName = document.getElementById("firstName").value;
	let lastName = document.getElementById("lastName").value;
	let login = document.getElementById("registerName").value;
	let password = document.getElementById("registerPassword").value;
//	var hash = md5( password );

	document.getElementById("registerResult").innerHTML = "";

	let tmp = {firstName:firstName,lastName:lastName,login:login,password:password};
//	var tmp = {firstName:firstName,lastName:lastName,login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/register.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				let jsonObject = JSON.parse( xhr.responseText );
				let msg = (jsonObject && jsonObject.message) ? jsonObject.message : "";

				if (!msg.toLowerCase().includes("success"))
				{
					document.getElementById("registerResult").innerHTML = "Registration failed";
					return;
				}

				window.firstName = firstName;
				window.lastName = lastName;

				saveCookie();
				window.location.href = "contact.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("registerResult").innerHTML = err.message;
	}
}

function doLogin()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
//	var hash = md5( password );
	
	document.getElementById("loginResult").innerHTML = "";

	let tmp = {login:login,password:password};
//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;
		
				if( userId < 1 )
				{		
					document.getElementById("loginResult").innerHTML = "Login failed";
					return;
				}
		
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();
	
				window.location.href = "contact.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

async function addContact()
{
	const resultEl = document.getElementById("contactAddResult");
	const newFirstName = document.getElementById("firstNameAdd").value.trim();
	const newLastName = document.getElementById("lastNameAdd").value.trim();
	const newEmail = document.getElementById("emailAdd").value.trim();
	const newPhone = document.getElementById("phoneAdd").value.trim();
	resultEl.innerHTML = "";

	if (!newFirstName || !newLastName || !newEmail || !newPhone)
	{
		resultEl.innerHTML = "Please fill out all fields.";
		return;
	}

	try
	{
		const exists = await contactNameExists(newFirstName, newLastName);
		if (exists)
		{
			resultEl.innerHTML = "A contact with that name already exists.";
			return;
		}
	}
	catch(err)
	{
		resultEl.innerHTML = "Unable to verify existing contacts.";
		return;
	}

	let tmp = {firstName:newFirstName,lastName:newLastName,email:newEmail,phone:newPhone,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/addContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState != 4)
			{
				return;
			}

			if (this.status !== 200)
			{
				resultEl.innerHTML = "Request failed (status " + this.status + ")";
				return;
			}

			try
			{
				const data = JSON.parse(this.responseText || "{}");
				if (data.error && data.error.length)
				{
					resultEl.innerHTML = data.error;
					return;
				}
			}
			catch(parseErr)
			{
				resultEl.innerHTML = "Contact was added, but response could not be parsed.";
				return;
			}

			resultEl.innerHTML = "Contact has been added";
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		resultEl.innerHTML = err.message;
	}
	
}
async function contactNameExists(firstName, lastName)
{
	return new Promise((resolve) =>
	{
		if (!userId)
		{
			resolve(false);
			return;
		}

		let searchPayload = {search:firstName,userId:userId};
		let url = urlBase + '/searchContact.' + extension;

		let xhr = new XMLHttpRequest();
		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
		xhr.onreadystatechange = function()
		{
			if (this.readyState != 4)
			{
				return;
			}

			if (this.status !== 200)
			{
				resolve(false);
				return;
			}

			try
			{
				const data = JSON.parse(this.responseText || '{}');
				if (data.error && data.error.length)
				{
					resolve(false);
					return;
				}

				const results = Array.isArray(data.results) ? data.results : [];
				const exists = results.some((c) =>
				{
					const f = (c.firstName || '').toLowerCase();
					const l = (c.lastName || '').toLowerCase();
					return f === firstName.toLowerCase() && l === lastName.toLowerCase();
				});
				resolve(exists);
			}
			catch(err)
			{
				resolve(false);
			}
		};

		xhr.send(JSON.stringify(searchPayload));
	});
}
function searchContact()
{
    let srch = document.getElementById("searchText").value;
    const resultEl = document.getElementById("contactSearchResult");
    const listEl = document.getElementById("contactList");
    resultEl.innerHTML = "";
    listEl.innerHTML = "";

    let tmp = {search:srch,userId:userId};
    let jsonPayload = JSON.stringify( tmp );

    let url = urlBase + '/searchContact.' + extension;
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function() 
        {
            if (this.readyState != 4)
            {
                return;
            }

            if (this.status !== 200)
            {
                resultEl.innerHTML = "Request failed (status " + this.status + ")";
                return;
            }

            let jsonObject = {};
            try
            {
                jsonObject = JSON.parse(this.responseText || "{}");
            }
            catch(err)
            {
                resultEl.innerHTML = "Unable to parse response";
                return;
            }

            if (jsonObject.error && jsonObject.error.length)
            {
                resultEl.innerHTML = jsonObject.error;
                return;
            }

            const results = Array.isArray(jsonObject.results) ? jsonObject.results : [];
            if (results.length === 0)
            {
                resultEl.innerHTML = "No contacts found.";
                return;
            }

            resultEl.innerHTML = "Contact(s) has been retrieved";

            let contactList = "";
            for( let i=0; i<results.length; i++ )
            {
                let c = results[i];
                contactList += (c.firstName || "") + " " + (c.lastName || "") + " / " + (c.email || "") + " / " + (c.phone || "") + " / " + (c.createdAt || "");
                if( i < results.length - 1 )
                {
                    contactList += "<br />\r\n";
                }
            }

            listEl.innerHTML = contactList;
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        resultEl.innerHTML = err.message;
    }
    
}

function deleteContact()
{
	const firstName = document.getElementById("deleteFirstName").value.trim();
	const lastName = document.getElementById("deleteLastName").value.trim();
	const resultEl = document.getElementById("contactDeleteResult");
	resultEl.innerHTML = "";

	if (!firstName || !lastName)
	{
		resultEl.innerHTML = "Please provide both the first and last name.";
		return;
	}

	if (!confirm("Are you sure you want to delete " + firstName + " " + lastName + "?"))
	{
		resultEl.innerHTML = "Deletion cancelled.";
		return;
	}

	let tmp = {firstName:firstName,lastName:lastName,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/deleteContact.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function ()
		{
			if (this.readyState != 4)
			{
				return;
			}

			if (this.status !== 200)
			{
				resultEl.innerHTML = "Request failed (status " + this.status + ")";
				return;
			}

			let response = {};
			try
			{
				response = JSON.parse(this.responseText || "{}");
			}
			catch(err)
			{
				resultEl.innerHTML = "Unable to parse response";
				return;
			}

			if (response.error)
			{
				resultEl.innerHTML = response.error;
				return;
			}

			const message = response.results ? response.results : "Contact deleted.";
			resultEl.innerHTML = message;
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		resultEl.innerHTML = err.message;
	}
}

function editContact()
{
	const originalFirstName = document.getElementById("editOriginalFirstName").value.trim();
	const originalLastName = document.getElementById("editOriginalLastName").value.trim();
	const newFirstName = document.getElementById("editFirstName").value.trim();
	const newLastName = document.getElementById("editLastName").value.trim();
	const newEmail = document.getElementById("editEmail").value.trim();
	const newPhone = document.getElementById("editPhone").value.trim();
	const resultEl = document.getElementById("contactEditResult");
	resultEl.innerHTML = "";

	if (!originalFirstName || !originalLastName)
	{
		resultEl.innerHTML = "Enter the current first and last name.";
		return;
	}

	if (!newFirstName || !newLastName || !newEmail || !newPhone)
	{
		resultEl.innerHTML = "Enter the updated first name, last name, email, and phone.";
		return;
	}

	let tmp = {
		originalFirstName: originalFirstName,
		originalLastName: originalLastName,
		firstName: newFirstName,
		lastName: newLastName,
		email: newEmail,
		phone: newPhone,
		userId: userId
	};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/editContact.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function ()
		{
			if (this.readyState != 4)
			{
				return;
			}

			if (this.status !== 200)
			{
				resultEl.innerHTML = "Request failed (status " + this.status + ")";
				return;
			}

			let response = {};
			try
			{
				response = JSON.parse(this.responseText || "{}");
			}
			catch(err)
			{
				resultEl.innerHTML = "Unable to parse response";
				return;
			}

			if (response.error)
			{
				resultEl.innerHTML = response.error;
				return;
			}

			resultEl.innerHTML = "Contact has been updated.";
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		resultEl.innerHTML = err.message;
	}
}

