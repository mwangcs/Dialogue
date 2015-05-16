#FoodSpeak: A Web Voice Interface for Restaurant Search
Synopsis
------

FoodSpeak is a web-based multimodal spoken dialogue system that enables users to search and get relevant information in restaurant domain. It is available online at: [Foodspeak](http://mandiw.com/sds/index.php)

Directory Structure
------

+-- NLG/  
+-- dialogueManager/  
|-------+-- lib/ (various style and authorization files)  
|-------+-- DM.php (Source code for the dialogue manager. This version uses baseline NLG)  
|-------+-- DM1.php (Source code for the dialogue manager. This version uses Ratcliff/Obershelp based NLG)  
|-------+-- DM2.php (Source code for the dialogue manager. This version uses Edit Distance based NLG)  
|-------+-- DMtest.php (A interface for testing the dialogue manager directly without speech input)  
|-------+-- requirements.txt (requirement file for using Yelp API)  
+-- microphone/ (microphone SDK provided by Wit.AI that enables interaction with our Wit.AI instance)  
+-- index.php (Main page of our web application. This version uses baseline NLG)  
+-- index1.php (Main page of our web application. This version uses Ratcliff/Obershelp based NLG)  
+-- index2.php (Main page of our web application. This version uses Edit Distance based NLG)  


Motivation
------

Yelp, Foursquare and other websites provide valuable service to consumers by allowing them to search for, browse and review local businesses. Customers are able to find and discover restaurants they like, by getting specific information about them, and even making table reservations. A voice interface for such applications allows the hands-free usage of such applications. This can be highly efficient, as users often use these applications when they are on the move. Typing in filters, and searching for various details through the small screen of their phones can become quite tedious, and cumbersome.  Finding these various filters and search controls also requires some small effort or familiarity with the particular system.  An effective voice search would eliminate the search for filters entirely.

Our goal is to build a spoken dialogue web application that lets users search for restaurants. This system recommends restaurants based on preferences specified by users, and provides information such as address, popularity, contact information and whether it’s currently open. We decided to develop a web application so that it avoids the burden in deploying in user machines, it is platform independent and it enables users to access it from anywhere on any device.

There are several challenges we are facing. A primary challenge is detection of a new query, and subsequently resetting filters to the request. Our solution is to develop keywords. For example, we could ask users to say phrases such as “I’m done!”  to represent end of conversation, “Go back!” to return to or undo last step, or “Start over” to reset filters and begin a new query. Another challenge is to efficiently present the information in order to enhance user experience. Given the generally large number of restaurants listed in each result and the amount of information associated with them, it is essential we optimize our information presentation. This is essential in order to ensure that the users are not overburdened by the information, and to minimize the number of dialogue turns required for the user to find an acceptable option. We used a combination of speech, text and pictures to optimize this.

System functionality
------

The major functionality of the system is to recommend restaurants to the user, based on his/her stated preferences. The system will use speech as input and the output will consist of a combination of template-generated speech and on-screen text and pictures. There are mainly two types of actions a user could perform. The first is to get recommendations of restaurants based on their preferences. An example is shown in Figure 1. The second is to get information about specific restaurants, as in Figure 2. For a specific restaurant, users can request information, such as phone number, address and star rating after a restaurant is selected. 


