#!/usr/bin/env python3
"""
Main entry point for Overpass AI operations.
Routes commands to appropriate handlers.
"""

import sys
import json

def main():
    try:
        # Read input from stdin
        input_data = json.loads(sys.stdin.read())
        command = input_data.get('command', 'vectorize')

        if command == 'vectorize':
            from vectorize import vectorize_text
            result = vectorize_text(input_data.get('text', ''))
            print(json.dumps(result))
        else:
            print(json.dumps({'error': f'Unknown command: {command}'}))

    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
