__author__ = "Ananya Poddar <ap3317@columbia.edu>"
__date__ = "$April, 2015"


# Response to restaurant query: ( By Professor Stoyanchev)
# Here is a list of  FOOD_TYPE  restaurants..
#  Here is a list of  ADJECTIVE FOOD_TYPE  restaurants.
#  Some of the FOOD_TYPE  restaurants NEARNESS are
# etc.

# ADJECTIVE -> great, delicious, wonderful, etc.
# NEARNESS-> near you, around you, nearby, etc.

# This will give you a variety of templates that could be chosen based on a user's utterance or randomly.


""" * Download Levenshtein, difflib & nltk * |
Levenshtein implements edit distance |
difflib is an advanced version of R/O | NLTK is being used for POS tagging"""

import random
import Levenshtein, difflib, nltk

"""
p1-
S : How may I help you today?
U : Give me a list of great italian restaurants.
p2-S : Awesome! Do you have any preferences for location?-p1
U : Yes, how about restaurants near me.
S : Okay, here is a list of restaurants near you. -p2

"""

# Steps for user utterance analysis:
# 1)* POS Tagging - If JJ in user_utterance, extract reaffirmation.
# 2)* On the basis of slot to be filled, randomly extract a sentence from <required> txt file
# 3)& If response to be returned to the users (after yelp api response obtained); Perform POS tagging + string, semantic similarity

simple_affirmations = ["Okay", "Why Not!", "Hmm"]
reaffirmations = ["Great! ", "Awesome! ", "That sounds great! ", "Okay, ", "Sure, ", "Why not! "]
nearness = ["near you", "around you", "nearby", "close by", "in your area"]

def if_adj(user_utterance, cuisine):
    """ i/p Utterance; If any pos = 'JJ', then return true, else false |
    User has the knack of using adjectives
    (other than the cuisine name) for expressiveness """

    flag = False

    # Adjust intent value #
    cuisine = "Italian restaurants"

    postag = nltk.pos_tag(nltk.word_tokenize(user_utterance))
    print postag
    for tag_pair in postag:
        if (('JJ' in tag_pair) or ('JJS' in tag_pair) and (tag_pair[0] not in cuisine)):
            # Second condition prevents case : "Italian" within "Italian restaurants" to be tagged as a 'JJ'#
            print tag_pair
            flag = True
            break
    return flag

def if_user_loc(user_location):
    filename = open("/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/nearness.txt")
    rlines = filename.readlines()
    sim_lines = get_similar_RO(user_location, rlines, 0.95)
    if len(sim_lines) > 0:
        print sim_lines
        return True
    else:
        return False


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
    print sim_lines
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
    print sim_lines
    return sim_lines


# @mode : (0, Baseline) | (1, difflib <R/O>) | (2, Levenshtein <Edit Distance>)

def analyse_utterance(user_utterance, intent, entity, mode):
    response = ""
    if (if_adj(user_utterance, "*")):
        # Initial part of response will contain a
        # 1) random reaffirmation 2) 'Similar'
        # reaffirmation based on the previous adjective that was used
        response = reaffirmations[random.randint(0, len(reaffirmations) - 1)]
    else:
        response = simple_affirmations[random.randint(0, len(simple_affirmations) - 1)]
    # Slot to be filled | 1) Random slot fill qt 2) 'Similar' slot fill question
    # 1. Location
    if entity == "location":
        filename = "/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/locationslotfill.txt"
        th = 0.7

    # 2. Cuisine
    elif entity == "cuisine":
        filename = "/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/cuisineslotfill.txt"
        th = 0.60

    # 3. Deals
    elif entity == "deals":
        filename = "/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/dealslotfill.txt"
        th = 0.6

    # 4. Sorting
    elif entity == "sort":
        filename = "/Users/ananyapoddar/PycharmProjects/NLG/Resources/SampleSentences/sortingslotfill.txt"
        th = 0.6

    # Open required NLG template file #
    rfile = open(filename)
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
    return response

#
# # from TemplateAnalyser import *
val = analyse_utterance("Best restaurants near Columbia University", "i", "cuisine", 1)
print val





# def main(user_utterance, intent, entity):
#     # a = analyse_utterance(user_utterance, intent, entity)

# def usage():
#     sys.stderr.write("""
#     Usage: Analyse user utterance | Return suitable template response.\n""")

# if __name__ == "__main__":
#   # if len(sys.argv) != 4:
#   #   usage()
#   #   sys.exit(1)
#   main(sys.argv[1], sys.argv[2], sys.argv[3])

# # Input will be - user utterance, intent, entity #
# val = if_user_loc("near me")
# print val

def put_dash(phone_no):
    # First check if dash present, if not, insert.
    dashed_no, incr, index = "", len(phone_no)/3, len(phone_no)/3
    if len(phone_no)>2:
        while index<len(phone_no):
            dashed_no += phone_no[:index]
            index += incr
    return dashed_no[:-1]


def get_restaurant_info(entity_name, entity_value, fname):
    """This function is called for restaurant info request
    - phone no, rating, address, review, is_open"""

    file = open(fname)
    rlines = file.readlines();
    sel_response = [random.randint(0, len(rlines) - 1)]
    if entity_name == 'phoneRequest':
        phone_no = entity_value
        if phone_no.find('-') == -1:
            entity_value = put_dash(phone_no)
    response_str = sel_response.replace("#", entity_value)
    return response_str


