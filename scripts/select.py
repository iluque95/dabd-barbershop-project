#!/usr/bin/env python

import MySQLdb

conn = MySQLdb.connect(user = "itiel",
                        passwd = "asd123",
                        host = "localhost",
                        port = 3306,
                        db = "perruqueria")
c = conn.cursor()


for i in range(1000):
	c.execute("SELECT hora_inici FROM slot WHERE hora_inici > CURRENT_TIME AND int_data = CURRENT_DATE")

# print all the first cell of all the rows
#for row in c.fetchall():
#    print row[0]


c.close()
