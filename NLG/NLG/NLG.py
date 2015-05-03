from flask import Flask, render_template, request
from try_some import *

app = Flask(__name__)
# This creates an application instance of the class flask

# The views are the handlers that
# respond to requests from web browsers or other clients.
# In Flask handlers are written as Python functions. Each
#  view function is mapped to one or more request URLs.
@app.route('/')
def hello_world():
    return 'Hello World!'

# url : http://127.0.0.1:5000/tryme/ananya
@app.route('/tryme/<name>')
def hello_there(name):
    return 'Hello ' + name;

# url : http://127.0.0.1:5000/tryme1?name=ananya1&age=100
@app.route('/tryme1')
def hello_there1():
    name = request.args.get('name')
    age = request.args.get('age')
    some = request.args.get('some')
    return 'Hello ' + name + "\n" + 'Age ' + age + some

@app.route('/index')
def index():
    val = try_fn("try_fn parameter")
    user = {'nickname': val}
    return render_template('index.html',
                           title_page='Home',
                           user=user)

# url : http://127.0.0.1:5000/restaurant_info?entity=entityname&entityvalue=value&filename = fname
@app.route('/restaurant_info')
def get_info(entity_name, entity_value):



    val = try_fn("try_fn parameter")
    user = {'nickname': val}
    return render_template('index.html',
                           title_page='Home',
                           user=user)

# @app.route('/display_restaurants')
# def index():
#     val = try_fn("try_fn parameter")
#     user = {'nickname': val}
#     return render_template('index.html',
#                            title_page='Home',
#                            user=user)

# # entity, input, current state
# @app.route('AnalyseTemplate')
# def get_template(file_name, intent)

if __name__ == '__main__':
    app.run()
