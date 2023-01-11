2023/01/10 19:40

Under certain conditions I have to 

* delete the kwynn.com-wide PHP session to force re-auth
* delete the gotokens collection entry and / or revoke access.  

Otherwise, the OAUTH sequence will go back and forth from my page (for an instant) to Goo's auth page.  

I suspect there are related issues about finding the right tokens.  I'm pretty sure on my own dev system the refresh_token is not being saved 
properly.  

Then there are more issues on the database side about when to expire / delete stuff.
