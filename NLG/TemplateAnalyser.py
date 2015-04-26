__author__="Ananya Poddar <ap3317@columbia.edu>"
__date__ ="$April, 2015"

# >>> import Levenshtein
# >>> Levenshtein.ratio('hello world', 'hello')
# 0.625
# >>> import difflib
# >>> difflib.SequenceMatcher(None, 'hello world', 'hello').ratio()
# 0.625
# >>> >>> importk
# >>> import nltk
# >>> text = nltk.word_tokenize("You look great today")
# >>> nltk.pos_tag(text)
# [('You', 'PRP'), ('look', 'VBP'), ('great', 'JJ'), ('today', 'NN')]


# Response to restaurant query: ( By Professor Stoyanchev)
#  Here is a list of  FOOD_TYPE  restaurants..
#  Here is a list of  ADJECTIVE FOOD_TYPE  restaurants.
#  Some of the FOOD_TYPE  restaurants NEARNESS are
# etc.

# ADJECTIVE -> great, delicious, wonderful, etc.
# NEARNESS-> near you, around you, nearby, etc.

# This will give you a variety of templates that could be chosen based on a user's utterance or randomly.


""" * Download Levenshtein, difflib & nltk * |
Levenshtein implements edit distance | 
difflib is an advanced version of R/O | NLTK is being used for POS tagging"""

import sys, random
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

reaffirmations = ["Great! ", "Awesome! ", "That sounds great! ", "Okay, ", "Sure, ", "Why not! "]

def if_adj(user_utterance, intent):
	""" i/p Utterance; If any pos = 'JJ', then return true, else false | User has the knack of using adjectives (other than the cuisine name) for expressiveness """ 
	flag = False

	# Adjust intent value #
	intent = "Italian restaurants"

	postag = nltk.pos_tag(nltk.word_tokenize(user_utterance))
	print postag
	for tag_pair in postag:
		if (('JJ' in tag_pair) and (tag_pair[0] not in intent)):
			# Second condition prevents case : "Italian" within "Italian restaurants" to be tagged as a 'JJ'#
			print tag_pair
			flag = True
			break
	return flag


def get_similar_RO(user_utterance, temp_flines, th = 0.25):
	""" Compares user utterance with appropriate file's template lines using RatCliffe Obershelp, appending them 
	to 'sim_lines' list; if cosine similarity is greater than threshold value
	 | <If none; it goes back to the Baseline function within calling function> """
	sim_lines = [] # will hold lines with greater than a certain threshold value
	for each_line in temp_flines:
		cos_sim = difflib.SequenceMatcher(None, user_utterance, each_line).ratio()
		print (each_line, cos_sim)
		if cos_sim >= th :
			sim_lines.append(each_line.rstrip('/n'))
	return sim_lines


def get_similar_Lev(user_utterance, temp_flines, th = 0.6):
	""" Compares user utterance with appropriate file's template lines using Edit Distance, appending them 
	to 'sim_lines' list; if cosine similarity is greater than threshold value
	 | <If none; it goes back to the Baseline function within calling function> """
	sim_lines = [] # will hold lines with greater than a certain threshold value
	for each_line in temp_flines:
		cos_sim = Levenshtein.ratio(user_utterance, each_line)
		print (each_line, cos_sim)
		if cos_sim >= th :
			sim_lines.append(each_line.rstrip('/n'))
	return sim_lines


# @mode : (0, Baseline) | (1, difflib <R/O>) | (2, Levenshtein <Edit Distance>)

def analyse_utterance(user_utterance, intent, entity, mode):
	response = ""
	if(if_adj(user_utterance, "*")):
		# Initial part of response will contain a 1) random reaffirmation 2) 'Similar' reaffirmation based on the previous adjective that was used
		response = reaffirmations[random.randint(0, len(reaffirmations)-1)]

	# Slot to be filled | 1) Random slot fill qt 2) 'Similar' slot fill question
	# 1. Location
	if(entity == "location"):
		filename = "Templates/locationslotfill.txt"

	# 2. Cuisine
	elif(entity == "cuisine"):
		filename = "Templates/cuisineslotfill.txt"

	# 3. Deals
	elif(entity == "deals"):
		filename = "Templates/dealslotfill.txt"

	# 4. Sorting
	elif(entity == "sort"):
		filename = "Templates/sortingslotfill.txt"

	# Open required NLG template file #
	rfile = open(filename)
	rlines = rfile.readlines()

	# Actual Response string # 

	# - R/O : difflib - #
	if(mode == 1):
		print "Calling R/O"
		ro_matched_lines = get_similar_RO(user_utterance, rlines)
		if(len(ro_matched_lines) == 0):
			# Go back to baseline system #
			mode = 0 
		else:
			print len(ro_matched_lines)
			response += ro_matched_lines[random.randint(0, len(ro_matched_lines)-1)]

	# - Edit Distance - #
	elif(mode == 2):
		print "Calling Levenshtein"
		lev_matched_lines = get_similar_Lev(user_utterance, rlines)
		if(len(lev_matched_lines) == 0):
			# Go back to baseline system #
			mode = 0 
		else:
			print len(lev_matched_lines)
			response += lev_matched_lines[random.randint(0, len(ro_matched_lines)-1)]

	# - RANDOM | Baseline - #
	if(mode == 0):
		print "Reverting to Baseline .. "
		response += rlines[random.randint(0, len(rlines)-1)]
	return response



# from TemplateAnalyser import *
val = analyse_utterance("List of great italian restaurants around me", "i", "location", 1)
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
