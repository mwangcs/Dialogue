from flask import Flask, render_template, request
from TemplateAnalyser import *

app = Flask(__name__)

@app.route('/')
def hello_world():
    return 'Hello World!'

# url : http://127.0.0.1:5000/analyse_utterance?utterance=Find me an italian restaurant&type=location
# http://127.0.0.1:5000/analyse_utterance?utterance=Find me an italian restaurant
@app.route('/analyse_utterance')
def index():
    try:
        utterance = request.args.get('utterance')
    except Exception, e:
        print "Error ", e
        utterance = "I want to eat"

    try:
        entity_type = request.args.get('type')
    except Exception, e:
        print "Error ", e
        entity_type = "location"
    value = analyse_utterance(str(utterance), "i", str(entity_type), 1)
    # value = str(utterance) + " | "+str(entity_type)
    return value

# url : http://0.0.0.0:8080/restaurant_info?entity_name=phoneRequest&entity_value=1234&utterance=Can you give me the phone number
#*******#
@app.route('/restaurant_info')
def get_info():
    entity_name = request.args.get('entity_name')
    entity_value = request.args.get('entity_value')
    utterance = request.args.get('utterance')
    val = get_restaurant_info(utterance, entity_name, entity_value)
    # val = str(entity_name) + " | "+ str(entity_value) + " | " + str(utterance)
    return val

# value = get_restaurant_info("phoneRequest", "1234", "/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/phoneRequest.txt")
# print value

@app.route('/greeting')
def get_greeting():
    greeting = get_greetings()
    return greeting


@app.route('/goodbye')
def get_goodbye():
    goodbye = get_bye()
    return goodbye

@app.route('/clarification')
def get_clarifications():
    clarification = get_clarification()
    return clarification

@app.route('/selectRestaurant')
def info_type_request():
    restaurant = request.args.get('restaurant')
    info_type = get_info_type(str(restaurant))
    return info_type

#*******#
@app.route('/displayRestaurant')
def disp_restaurant():
    utterance = request.args.get('utterance')
    response = restaurant_display(str(utterance))
    return response

@app.errorhandler(500)
def internal_error(error):
    return "500 error"

@app.errorhandler(404)
def not_found(error):
    return "404 error"


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=80, debug=False)

