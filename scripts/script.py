#!/usr/bin/env python

import MySQLdb
from faker import Faker
import random
import string
from datetime import *

def id_generator(size=20, chars=string.ascii_uppercase + string.digits):
    return ''.join(random.choice(chars) for _ in range(size))

def do_query(c, q, n=''):
    if opt.show:
        print("Query is: " + q + " with values " + str(n))
    c.execute(q, n)


conn = MySQLdb.connect(user = "itiel",
                        passwd = "asd123",
                        host = "localhost",
                        port = 3306,
                        db = "perruqueria")
c = conn.cursor()

c.execute("SELECT * FROM client")

# print all the first cell of all the rows
for row in c.fetchall():
    print row[0]

fake = Faker('es_ES')

# INSERTA USUARIS
q = "INSERT INTO client VALUES (%s, %s, %s, %s, %s, 0, 0);"
for x in range(10000):

    try:
        do_query(c, q, (fake.free_email(), id_generator(), fake.first_name(), fake.last_name(), fake.phone_number()))
        conn.commit()
    except:
        print("Duplicated entry!!")

c.close()
