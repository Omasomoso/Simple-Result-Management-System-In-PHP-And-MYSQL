import sqlite3

# Connect to SQLite database (creates it if it doesn't exist)
conn = sqlite3.connect('school_management.db')
cursor = conn.cursor()

# Read and execute the SQL schema
with open('school_results.sql', 'r') as sql_file:
    sql_script = sql_file.read()

# Execute the entire script
cursor.executescript(sql_script)

# Commit changes and close connection
conn.commit()
conn.close()