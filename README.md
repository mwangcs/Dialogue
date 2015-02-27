#Voice Interface for Restaurant Search
* **Motivation**

Yelp, Foursquare and many other companies provide local business information that helps customers to find and discover the restaurants they like. A voice interface for those websites are in demand because users often search for such information on their phone and it is hard for them to type especially when they are outside. Our goal is to build a spoken dialogue system to let users search for restaurants. This system recommends restaurants based on preferences specified by users, and provides information such as address, hours of operation and telephone number.
There are several challenges we are facing. The first challenge is detecting end of conversation and identifying a new query to reset filters. One possible solution is to develop keywords. For example, we could ask users to say “Hooray!” to represent end of conversation. Another challenge is to efficiently present the information to user to give them the best experience. Given the large number of restaurants and amount of information associated with each restaurant, it is hard to present all the relevant information to users. We plan to use a combination of speech, text and pictures to optimize this.

* **System functionality**

The system will use speech as input and the output will consist of a combination of template­generated speech, text and pictures. There are mainly two types of actions a user could perform. The first one is to get recommendations of restaurants based on their preferences. An example is shown in Figure 1. The second one is to get information of restaurants, as in Figure 2. For a specific restaurant, users can request information, such as phone number, photos, distance and deals.
The domain concepts of our system includes price, location, rating and cuisine. Listed below are some example concepts:

   1. price: low, moderate, high
   2. rating: One Star, Two Stars, Three Stars, Four Stars, Five Stars
   3. cuisine: Indian, J​apanese, Thai
   4. location: Upper West Side, Morningside Heights, nearby, within one mile

![Sample Dialogue 1](http://i.imgur.com/voYykBz.jpg)
![Sample Dialogue 2](http://i.imgur.com/khTuWSG.jpg)

* **Implementation**

![Control Flow](http://i.imgur.com/WyOqx4A.jpg)

Figure 3 shows the control flow of our dialogue system. Wit.AI framework will be used for speech recognition and natural language understanding. Our system will interface with the Yelp API. This allows for expandability in the future. A parser, which parses JSON objects returned by wit.AI and generates requests to query information from Yelp Search API, will be implemented. Google Location Services API will be integrated to the system to add location awareness to the application. The dialogue manager keeps track of the information and requirements offered by the users, and generates prompts to ask for additional filters to narrow down search results. The natural language generation component will be rule­based and we will draft the templates. Android Speech API will be used to convert the text output to speech.

* **Evaluation**

Our main focus is on system development and we aim to develop an end­to­end fully functioning mobile app. We may also look into research questions related to information presentation. To evaluate our system, we will create an evaluation questionnaire. The survey will have questions based on level of measurement as well as open­ended questions asking for feedbacks. We intend to ask users to test and interact with our system and fill out the evaluation questionnaire to judge their satisfaction with their experience.

