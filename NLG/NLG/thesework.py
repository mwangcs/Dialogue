def get_restaurant_info(entity_name, entity_value, fname):
    """This function is called for restaurant info request
    - phone no, rating, address, review, is_open"""
    if entity_name == "infoError":
        error_fname = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/infoError.txt")
        rlines = error_fname.readlines()
        response_str = rlines[random.randint(0, len(rlines) - 1)]
        
    else:
        file = open(fname)
        rlines = file.readlines()
        sel_response = rlines[random.randint(0, len(rlines) - 1)]
        if sel_response.find('#') != -1:
            response_str = sel_response.replace("#", entity_value)
    return response_str


# rstr = get_restaurant_info("addressRequest", "66W, 109th Street", "/Users/ananyapoddar/PycharmProjects/NLG/Resources/S"
#                                                                   + "ampleSentences/addressRequest.txt")
# print rstr

def get_greetings():
    rfile = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/greetings.txt")
    rlines = rfile.readlines()
    return rlines[random.randint(0, len(rlines) - 1)]

def get_bye():
    rfile = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/goodbye.txt")
    rlines = rfile.readlines()
    return rlines[random.randint(0, len(rlines) - 1)]

def get_clarification():
    rfile = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/clarification.txt")
    rlines = rfile.readlines()
    return rlines[random.randint(0, len(rlines) - 1)]

def display_restaurants():
    return "abc"

def get_info_type(restaurant_name):
    rfile = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/selectRestaurant.txt")
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    if sel_response.find('#') != -1:
        response_str = sel_response.replace("#", restaurant_name)
    return response_str


def restaurant_display(utterance):
    rfile = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/displayRestaurants.txt")
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    response_str = "Analyse this text " + sel_response.rstrip('\n') + " | "+str(utterance)
    return response_str

# val = restaurant_display("hey there")
