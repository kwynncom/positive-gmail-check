
2023/01/10 

23:44

On third thought, the problem is that I am not writing uniquely to the tables / collections.  That is, sessions and addrs have multiple tokens

*****
22:31

Regarding the following, I think I know part of what's wrong.  Now that I have cookies (including the PHP session ID) set to strict or whatever the 
setting is that does not allow them to come from outside links, my code doesn't know whom the OAUTH code is for.

23:50 - That's not true.  The session starts before the token is written to the DB.  Then the redirect is internal, so the session is alive.


****
2023/01/10 19:40

Under certain conditions I have to 

* delete the kwynn.com-wide PHP session to force re-auth
* delete the gotokens collection entry and / or revoke access.  

Otherwise, the OAUTH sequence will go back and forth from my page (for an instant) to Goo's auth page.  

I suspect there are related issues about finding the right tokens.  I'm pretty sure on my own dev system the refresh_token is not being saved 
properly.  

Then there are more issues on the database side about when to expire / delete stuff.
