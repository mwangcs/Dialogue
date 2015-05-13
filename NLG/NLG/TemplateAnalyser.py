__author__ = "Ananya Poddar <ap3317@columbia.edu>"
__date__ = "$April, 2015"


# Response to restaurant query: ( By Professor Stoyanchev)
# Here is a list of  FOOD_TYPE  restaurants..
# Here is a list of  ADJECTIVE FOOD_TYPE  restaurants.
#  Some of the FOOD_TYPE  restaurants NEARNESS are
# etc.

# ADJECTIVE -> great, delicious, wonderful, etc.
# NEARNESS-> near you, around you, nearby, etc.

# This will give you a variety of templates that could be chosen based on a user's utterance or randomly.


""" * Download Levenshtein, difflib & nltk * |
Levenshtein implements edit distance |
difflib is an advanced version of R/O | NLTK is being used for POS tagging"""

import random, urllib2
import Levenshtein, difflib, nltk

"""
p1-
S : How may I help you today?
U : Give me a list of great italian restaurants.
p2-S : Awesome! Do you have any preferences for location?-p1
U : Yes, how about restaurants near me.
S : Okay, here is a list of restaurants near you. -p2

"""

simple_affirmations = ["Okay, ", "Let me find out! ", "Let's see, ", "I believe, "]
reaffirmations = ["Great! ", "Awesome! ", "That sounds great! ", "Okay, ", "Sure, ", "Why not! "]
nearness = ["near you ", "around you ", "nearby ", "close by ", "in your area "]


def get_similar_RO(user_utterance, temp_flines, th):
    """ Compares user utterance with appropriate file's template lines using RatCliffe Obershelp, appending them
    to 'sim_lines' list; if cosine similarity is greater than threshold value
     | <If none; it goes back to the Baseline function within calling function> """
    sim_lines = []  # will hold lines with greater than a certain threshold value
    for each_line in temp_flines:
        sentence = each_line.split('\t')
        user_tr = sentence[0]
        # print user_tr
        cos_sim = difflib.SequenceMatcher(None, user_utterance, user_tr).ratio()
        print (each_line, cos_sim)
        if cos_sim >= th:
            sim_lines.append(sentence)
    # print sim_lines
    return sim_lines


def get_similar_Lev(user_utterance, temp_flines, th):
    """ Compares user utterance with appropriate file's template lines using Edit Distance, appending them
    to 'sim_lines' list; if cosine similarity is greater than threshold value
     | <If none; it goes back to the Baseline function within calling function> """
    sim_lines = []  # will hold lines with greater than a certain threshold value
    for each_line in temp_flines:
        sentence = each_line.split('\t')
        user_tr = sentence[0]
        cos_sim = Levenshtein.ratio(user_utterance, user_tr)
        print (each_line, cos_sim)
        if cos_sim >= th:
            sim_lines.append(sentence)
    # print sim_lines
    return sim_lines

# 52.7.6.223 | http://52.7.6.223/analyse_utterance?utterance=eat&type=location
# @mode : (0, Baseline) | (1, difflib <R/O>) | (2, Levenshtein <Edit Distance>)
# http://0.0.0.0/analyse_utterance?utterance=eat&type=location
def analyse_utterance(user_utterance, intent, entity, mode):
    response = ""
    if entity == "location" or entity == "search_query":
        response = reaffirmations[random.randint(0, len(reaffirmations) - 1)]
    else:
        response = simple_affirmations[random.randint(0, len(simple_affirmations) - 1)]
    # 1. Location
    if entity == "location":
        filename = "http://mandiw.com/sds/nlgtext/locationslotfill.txt"
        th = 0.7

    # 2. Cuisine
    elif entity == "search_query":
        filename = "http://mandiw.com/sds/nlgtext/cuisineslotfill.txt"
        th = 0.60

    #### Info ####
    # 3. addressRequest 4. isOpenRequest
    # 5. phoneRequest # 6. ratingRequest    # 7. reviewRequest
    elif ((entity == "addressRequest") or (entity == "isOpenRequest") or (entity == "phoneRequest") or (
        entity == "ratingRequest") or (entity == "reviewRequest")):
        filename = "http://mandiw.com/sds/nlgtext/" + str(entity) + ".txt"
        th = 0.70

    rfile = urllib2.urlopen(filename)
    # rfile = open(filename)
    rlines = rfile.readlines()

    # Actual Response string #

    # - R/O : difflib - #
    if mode == 1:
        print "Calling R/O"
        ro_matched_lines = get_similar_RO(user_utterance, rlines, th)
        if len(ro_matched_lines) == 0:
            # Go back to baseline system #
            mode = 0
        else:
            print len(ro_matched_lines)
            response += ro_matched_lines[random.randint(0, len(ro_matched_lines) - 1)][1]

    # - Edit Distance - #
    elif mode == 2:
        print "Calling Levenshtein"
        lev_matched_lines = get_similar_Lev(user_utterance, rlines, th)
        if len(lev_matched_lines) == 0:
            # Go back to baseline system #
            mode = 0
        else:
            print len(lev_matched_lines)
            response += lev_matched_lines[random.randint(0, len(ro_matched_lines) - 1)][1]

    # - RANDOM | Baseline - #
    if mode == 0:
        print "Reverting to Baseline .. "
        rand_line = rlines[random.randint(0, len(rlines) - 1)]
        sentence = rand_line.split('\t')
        response += sentence[1]
    return response.rstrip('\n')


# from TemplateAnalyser import *
# val = analyse_utterance("Find me a chinese restaurant.", "i", "location", 1)
# print val

# # Input will be - user utterance, intent, entity #
# val = if_user_loc("near me")
# print val

def get_restaurant_info(utterance, entity_name, entity_value):
    """This function is called for restaurant info request
    - phone no, rating, address, review, is_open"""
    if entity_name == "infoError":
        filename = "http://mandiw.com/sds/nlgtext/infoError.txt"
        # error_fname = open("http://mandiw.com/sds/nlgtext/infoError.txt")
        error_fname = urllib2.urlopen(filename)
        rlines = error_fname.readlines()
        sel_response = rlines[random.randint(0, len(rlines) - 1)]

    else:
        sel_response = analyse_utterance(utterance, "i",  str(entity_name), 1)
        # sel_response = sim_lines[random.randint(0, len(sim_lines) - 1)]
        if sel_response.find('#') != -1:
            sel_response = sel_response.replace("#", entity_value)
    return sel_response.rstrip('\n')

#
# rstr = get_restaurant_info("Can you tell me the address","addressRequest", "66W, 109th Street")
# rstr = get_restaurant_info("Is it open","isOpenRequest", "closed")
# rstr = get_restaurant_info("Can you tell me the phone number","phoneRequest", "66-109")
# rstr = get_restaurant_info("Can you tell me the rating","ratingRequest", "5")
# rstr = get_restaurant_info("What is the review","reviewRequest", "I had a great time!")
# print rstr

def get_greetings():
    # rfile = open("http://mandiw.com/sds/nlgtext/greetings.txt")
    filename = "http://mandiw.com/sds/nlgtext/greetings.txt"
    rfile = urllib2.urlopen(filename)
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    return sel_response.rstrip('\n')


def get_bye():
    # rfile = open("http://mandiw.com/sds/nlgtext/goodbye.txt")
    filename = "http://mandiw.com/sds/nlgtext/goodbye.txt"
    rfile = urllib2.urlopen(filename)
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    return sel_response.rstrip('\n')
    # return rlines[random.randint(0, len(rlines) - 1)]


def get_clarification():
    # rfile = open("http://mandiw.com/sds/nlgtext/clarification.txt")
    filename = "http://mandiw.com/sds/nlgtext/clarification.txt"
    rfile = urllib2.urlopen(filename)
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    return sel_response.rstrip('\n')



def get_info_type(restaurant_name):
    # rfile = open("http://mandiw.com/sds/nlgtext/selectRestaurant.txt")
    filename = "http://mandiw.com/sds/nlgtext/selectRestaurant.txt"
    rfile = urllib2.urlopen(filename)
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    if sel_response.find('#') != -1:
        sel_response = sel_response.replace("#", restaurant_name)
    return sel_response.rstrip('\n')


def restaurant_display(utterance):
    # rfile = open("http://mandiw.com/sds/nlgtext/displayRestaurants.txt")
    filename = "http://mandiw.com/sds/nlgtext/displayRestaurants.txt"
    rfile = urllib2.urlopen(filename)
    rlines = rfile.readlines()
    sel_response = rlines[random.randint(0, len(rlines) - 1)]
    return sel_response.rstrip('\n')

# http://mandiw.com/sds/nlgtext/

# /Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/